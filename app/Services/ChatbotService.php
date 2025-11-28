<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Category;
use OpenAI;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $client;
    protected $isMock;
    protected $provider;

    public function __construct()
    {
        $this->isMock = env('CHATBOT_MOCK', false);
        $this->provider = env('CHATBOT_PROVIDER', 'openai');
        $apiKey = env('OPENAI_API_KEY');
        
        if ($apiKey && !$this->isMock && $this->provider === 'openai') {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function processMessage(User $user, string $message)
    {
        $context = $this->getFinancialContext($user);

        if ($this->isMock) {
            return $this->generateMockResponse($message, $context);
        }

        try {
            $systemPrompt = "You are a helpful and intelligent Financial Assistant for a budget application. 
            Your goal is to help the user manage their money, understand their spending habits, and provide actionable advice.
            
            Here is the user's current financial context (JSON format):
            " . json_encode($context) . "
            
            Guidelines:
            1. Be concise, friendly, and encouraging.
            2. Use the provided context to answer specific questions (e.g., 'How much did I spend on food?').
            3. If the user asks for advice (e.g., 'Where can I save?'), analyze their spending patterns in the context and give specific suggestions.
            4. If you don't have enough information in the context to answer a specific question, politely explain what you know and what you don't.
            5. Format your response using Markdown (e.g., bold for amounts, lists for categories).
            6. Do not make up data. Only use what is provided.
            ";

            if ($this->provider === 'gemini') {
                $result = Gemini::generativeModel(model: 'gemini-pro-latest')->generateContent($systemPrompt . "\n\nUser Question: " . $message);
                return $result->text();
            }

            if (!$this->client) {
                 return $this->generateMockResponse($message, $context);
            }

            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            // Fallback to mock response on error
            if (str_contains($e->getMessage(), 'Rate limit') || str_contains($e->getMessage(), 'Connection') || str_contains($e->getMessage(), 'quota')) {
                return $this->generateMockResponse($message, $context) . "\n\n*(Note: Switched to offline mode due to API connection issues.)*";
            }
            return "I'm having trouble connecting to my brain right now. Please try again later. (Error: " . $e->getMessage() . ")";
        }
    }

    protected function generateMockResponse($message, $context)
    {
        $msg = strtolower($message);
        $summary = $context['summary'];
        $month = $context['month'];

        // Synonyms mapping
        $synonyms = [
            'food' => ['groceries', 'dining', 'restaurants', 'fast food', 'snacks', 'eating out'],
            'transport' => ['fuel', 'gas', 'uber', 'taxi', 'bus', 'train', 'car'],
            'utilities' => ['electric', 'water', 'internet', 'gas', 'phone', 'mobile'],
            'entertainment' => ['movies', 'games', 'netflix', 'spotify', 'hobbies'],
            'shopping' => ['clothes', 'electronics', 'amazon', 'gifts'],
            'health' => ['doctor', 'pharmacy', 'gym', 'medical'],
        ];

        // Spending / Expenses
        if (str_contains($msg, 'spend') || str_contains($msg, 'expense') || str_contains($msg, 'cost')) {
            $foundCategories = [];
            
            // 1. Direct Match
            foreach ($context['category_spending'] as $cat) {
                if (str_contains($msg, strtolower($cat['category']))) {
                    $foundCategories[] = $cat;
                }
            }

            // 2. Synonym Match
            if (empty($foundCategories)) {
                foreach ($synonyms as $key => $values) {
                    if (str_contains($msg, $key)) {
                        // User asked for a general term (e.g. "food"), look for specific categories
                        foreach ($context['category_spending'] as $cat) {
                            $catName = strtolower($cat['category']);
                            if (in_array($catName, $values) || str_contains($catName, $key)) {
                                $foundCategories[] = $cat;
                            }
                        }
                    }
                }
            }

            if (!empty($foundCategories)) {
                $response = "In **{$month}**, here is your spending for that:";
                foreach ($foundCategories as $cat) {
                    $response .= "\n- **{$cat['category']}**: \${$cat['amount']}";
                }
                return $response;
            }
            
            // If asking for "total" or just general spending
            if (str_contains($msg, 'total') || str_contains($msg, 'overall') || str_contains($msg, 'everything')) {
                 return "Your total spending for **{$month}** is **\${$summary['expense']}**.";
            }

            // Fallback: List top categories to help user
            $topCats = array_slice($context['category_spending'], 0, 5);
            $catList = array_map(fn($c) => "- **{$c['category']}**: \${$c['amount']}", $topCats);
            $catString = implode("\n", $catList);
            
            return "I couldn't find a specific category matching your question. \n\nYour total spending for **{$month}** is **\${$summary['expense']}**.\n\nHere are your top expenses you can ask about:\n{$catString}";
        }

        // Savings
        if (str_contains($msg, 'save') || str_contains($msg, 'saving')) {
            $savings = $summary['savings'];
            if ($savings > 0) {
                return "Great job! You have saved **\${$savings}** so far in **{$month}**. Keep it up!";
            } else {
                return "Currently, your expenses exceed your income by **\$" . abs($savings) . "**. Try to cut back on discretionary spending.";
            }
        }

        // Biggest Expense
        if (str_contains($msg, 'biggest') || str_contains($msg, 'highest') || str_contains($msg, 'most')) {
            if (!empty($context['category_spending'])) {
                $top = $context['category_spending'][0];
                return "Your biggest expense category this month is **{$top['category']}** with a total of **\${$top['amount']}**.";
            }
            return "I don't have enough data to determine your biggest expense yet.";
        }

        // Default / Help
        return "I'm in **Demo Mode**. I can answer questions like:\n- \"How much did I spend on Food?\"\n- \"What is my total spending?\"\n- \"How much have I saved?\"\n- \"What is my biggest expense?\"";
    }

    protected function getFinancialContext(User $user)
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Total Spending & Income this month
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->sum('amount');

        // 2. All Category Spending (Sorted by Amount)
        $categorySpending = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Uncategorized',
                    'amount' => $item->total
                ];
            });

        // 3. Budget Status (Keep for AI context)
        $budgets = Budget::where('user_id', $user->id)
            ->with('category')
            ->get()
            ->map(function ($budget) use ($user, $startOfMonth, $endOfMonth) {
                $spent = Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->where('date', '>=', $startOfMonth)
                    ->where('date', '<=', $endOfMonth)
                    ->sum('amount');
                
                return [
                    'category' => $budget->category->name,
                    'limit' => $budget->amount,
                    'spent' => $spent,
                    'remaining' => $budget->amount - $spent,
                    'status' => $spent > $budget->amount ? 'Over Budget' : 'On Track'
                ];
            });

        // 4. Recent Transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->latest('date')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(function ($t) {
                return "{$t->date->format('M d')}: {$t->description} ({$t->type}) - \${$t->amount} [" . ($t->category->name ?? 'No Category') . "]";
            });

        return [
            'month' => $now->format('F Y'),
            'summary' => [
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
                'savings' => $monthlyIncome - $monthlyExpense
            ],
            'category_spending' => $categorySpending->values()->toArray(),
            'budgets' => $budgets->values()->toArray(),
            'top_expenses' => $categorySpending->take(5)->values()->toArray(),
            'recent_transactions' => $recentTransactions->values()->toArray()
        ];
    }
}
