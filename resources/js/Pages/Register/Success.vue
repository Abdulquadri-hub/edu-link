<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// const props = defineProps({
//   email: String,
//   message: String
// });

// const isResending = ref(false);
// const resendMessage = ref('');

const props = defineProps({
  status: String,
});

const isResending = ref(false);
const resendMessage = ref(props.status);

const resendVerification = async () => {
  isResending.value = true;
  resendMessage.value = '';
  
  try {
    await router.post('/email/verification-notification', {
      email: props.email
    }, {
      preserveScroll: true,
      onSuccess: () => {
        resendMessage.value = 'Verification email sent successfully!';
        isResending.value = false;
      },
      onError: () => {
        resendMessage.value = 'Failed to send email. Please try again.';
        isResending.value = false;
      }
    });
  } catch (error) {
    resendMessage.value = 'An error occurred. Please try again.';
    isResending.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <a href="/" class="inline-flex items-center space-x-3 group mb-6">
          <div class="w-12 h-12 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-all duration-300 shadow-lg">
            <span class="text-2xl font-bold text-white">E</span>
          </div>
          <span class="text-2xl font-bold bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            EduLink
          </span>
        </a>
      </div>

      <!-- Success Card -->
      <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-fade-in-up">
        <!-- Animated Checkmark -->
        <div class="mb-6">
          <div class="mx-auto w-20 h-20 bg-linear-to-r from-emerald-500 to-teal-600 rounded-full flex items-center justify-center animate-bounce">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-4">Registration Successful!</h2>
        
        <p class="text-gray-600 mb-2">Your account has been created successfully.</p>
        <p class="text-gray-600 mb-6">A verification email has been sent to:</p>
        
        <div class="inline-block px-6 py-3 bg-emerald-50 rounded-lg mb-8">
          <p class="font-semibold text-emerald-600">{{ email || 'your email address' }}</p>
        </div>

        <p class="text-gray-600 mb-8">
          {{ message || 'Please check your inbox and verify your email address to activate your account.' }}
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <a
            href="/login"
            class="inline-flex items-center justify-center px-8 py-3 bg-linear-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-lg"
          >
            Go to Login
          </a>
          <a
            href="/"
            class="inline-flex items-center justify-center px-8 py-3 border-2 border-emerald-500 text-emerald-600 font-bold rounded-lg hover:bg-emerald-50 transition-all duration-200"
          >
            Back to Home
          </a>
        </div>

        <div class="border-t pt-6">
          <p class="text-gray-600 text-sm mb-3">Didn't receive the email?</p>
          <button
            @click="resendVerification"
            :disabled="isResending"
            class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm hover:underline disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isResending ? 'Sending...' : 'Resend Verification Email' }}
          </button>
          
          <p v-if="resendMessage" :class="resendMessage.includes('success') ? 'text-green-600' : 'text-red-600'" class="text-sm mt-2">
            {{ resendMessage }}
          </p>
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