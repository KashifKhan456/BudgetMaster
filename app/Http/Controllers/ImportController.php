<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        // Remove header row
        $header = array_shift($data);

        // Basic validation of header structure (optional but good practice)
        // Expected: Date, Type, Category, Amount, Description

        $count = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                if (count($row) < 5) continue; // Skip invalid rows

                // Map row to variables (assuming order: Date, Type, Category, Amount, Description)
                // Adjust mapping based on your export format or expected import format
                $date = $row[0];
                $type = strtolower($row[1]);
                $categoryName = $row[2];
                $amount = $row[3];
                $description = $row[4] ?? null;

                // Find or create category
                $category = Category::firstOrCreate(
                    ['name' => $categoryName, 'user_id' => Auth::id()],
                    ['type' => $type, 'color' => '#cccccc'] // Default color
                );

                $validator = Validator::make([
                    'date' => $date,
                    'type' => $type,
                    'amount' => $amount,
                ], [
                    'date' => 'required|date',
                    'type' => 'required|in:expense,income',
                    'amount' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Transaction::create([
                    'user_id' => Auth::id(),
                    'category_id' => $category->id,
                    'amount' => $amount,
                    'type' => $type,
                    'date' => $date,
                    'description' => $description,
                ]);

                $count++;
            }

            DB::commit();

            if (count($errors) > 0) {
                return redirect()->back()->with('warning', "Imported $count transactions. Some rows failed: " . implode('; ', array_slice($errors, 0, 5)));
            }

            return redirect()->back()->with('success', "Imported $count transactions successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
