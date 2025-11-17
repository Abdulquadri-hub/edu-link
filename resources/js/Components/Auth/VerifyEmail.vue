<script setup>

import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  status: String,
});

const isResending = ref(false);

const message = ref(props.status);

const resendVerification = () => {
  isResending.value = true;
  message.value = '';
  
  router.post('/email/verification-notification', {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.value = 'Verification link sent! Please check your email.';
      isResending.value = false;
    },
    onError: () => {
      message.value = 'Failed to send verification email. Please try again.';
      isResending.value = false;
    }
  });
};
</script>

<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center space-x-3 group mb-6">
          <div class="w-12 h-12 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-all duration-300 shadow-lg">
            <span class="text-2xl font-bold text-white">E</span>
          </div>
          <span class="text-2xl font-bold bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            EduLink
          </span>
        </Link>
      </div>

      <!-- Verification Card -->
      <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-fade-in-up">
        <!-- Email Icon -->
        <div class="mb-6">
          <div class="mx-auto w-20 h-20 bg-linear-to-r from-emerald-500 to-teal-600 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-4">Verify Your Email</h2>
        
        <p class="text-gray-600 mb-6">
          Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
        </p>

        <p class="text-gray-600 mb-8">
          If you didn't receive the email, we'll gladly send you another.
        </p>

        <!-- Success Message -->
        <div v-if="message" class="mb-6 p-4 rounded-lg" :class="message.includes('sent') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
          {{ message }}
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <button
            @click="resendVerification"
            :disabled="isResending"
            class="inline-flex items-center justify-center px-8 py-3 bg-linear-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isResending" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isResending ? 'Sending...' : 'Resend Verification Email' }}
          </button>
          
          <Link
            href="/logout"
            method="post"
            as="button"
            class="inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all duration-200"
          >
            Logout
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out;
}
</style>
