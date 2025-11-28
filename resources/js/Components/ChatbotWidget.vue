<script setup>
import { ref, nextTick, watch } from 'vue';
import axios from 'axios';
import { marked } from 'marked';

const isOpen = ref(false);
const message = ref('');
const messages = ref([
    { role: 'assistant', content: 'Hello! I am your AI Financial Assistant. Ask me anything about your budget, spending, or savings.' }
]);
const isThinking = ref(false);
const chatContainer = ref(null);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!message.value.trim() || isThinking.value) return;

    const userMsg = message.value;
    messages.value.push({ role: 'user', content: userMsg });
    message.value = '';
    isThinking.value = true;
    scrollToBottom();

    try {
        const response = await axios.post(route('chatbot.message'), { message: userMsg });
        messages.value.push({ role: 'assistant', content: response.data.response });
    } catch (error) {
        console.error(error);
        messages.value.push({ role: 'assistant', content: "I'm sorry, I encountered an error. Please try again." });
    } finally {
        isThinking.value = false;
        scrollToBottom();
    }
};

const renderMarkdown = (text) => {
    return marked(text);
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Chat Window -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div v-if="isOpen" class="mb-4 w-96 h-[500px] bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="p-4 bg-indigo-600 dark:bg-indigo-900 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <h3 class="text-white font-semibold">Financial Assistant</h3>
                    </div>
                    <button @click="toggleChat" class="text-white/80 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Messages -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-[#1a1a1a]">
                    <div v-for="(msg, index) in messages" :key="index" 
                        :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                        <div :class="[
                            'max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm',
                            msg.role === 'user' 
                                ? 'bg-indigo-600 text-white rounded-br-none' 
                                : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-bl-none border border-gray-200 dark:border-gray-700'
                        ]">
                            <div v-if="msg.role === 'assistant'" v-html="renderMarkdown(msg.content)" class="prose dark:prose-invert prose-sm max-w-none"></div>
                            <div v-else>{{ msg.content }}</div>
                        </div>
                    </div>
                    
                    <!-- Thinking Indicator -->
                    <div v-if="isThinking" class="flex justify-start">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl rounded-bl-none px-4 py-3 border border-gray-200 dark:border-gray-700 shadow-sm flex gap-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 bg-white dark:bg-[#1a1a1a] border-t border-gray-200 dark:border-gray-700">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input 
                            v-model="message" 
                            type="text" 
                            placeholder="Ask about your finances..." 
                            class="flex-1 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm px-4 py-2"
                            :disabled="isThinking"
                        >
                        <button 
                            type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!message.trim() || isThinking"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Toggle Button -->
        <button 
            @click="toggleChat"
            class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-lg transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
/* Custom scrollbar for chat window */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style>
