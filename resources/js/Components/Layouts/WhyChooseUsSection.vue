<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';


const benefits = [
  {
    icon: '👥',
    stat: '15+',
    statLabel: 'Years Experience',
    title: 'Experienced Team',
    description: 'Our team combines decades of educational technology expertise with deep understanding of Nigerian school systems.',
    gradient: 'from-blue-500 to-blue-600'
  },
  {
    icon: '🇳🇬',
    stat: '100%',
    statLabel: 'Nigerian-Focused',
    title: 'Built for Nigeria',
    description: 'Designed specifically for Nigerian schools with local curriculum support, WAEC/NECO integration, and Naira payment options.',
    gradient: 'from-green-500 to-emerald-600'
  },
  {
    icon: '🎯',
    stat: 'All-in-One',
    statLabel: 'Complete Solution',
    title: 'Everything You Need',
    description: 'No need for multiple tools. From student management to parent communication, everything is integrated in one powerful platform.',
    gradient: 'from-purple-500 to-purple-600'
  },
  {
    icon: '⚡',
    stat: '24/7',
    statLabel: 'Support Available',
    title: 'Always Here for You',
    description: 'Get help anytime with our local Nigerian support team. Quick responses in English, Pidgin, Yoruba, Igbo, and Hausa.',
    gradient: 'from-orange-500 to-orange-600'
  }
];

</script>

<template>
  <section class="relative py-24 bg-linear-to-b from-gray-50 to-white overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-200 rounded-full blur-3xl opacity-20"></div>
      <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-teal-200 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="relative container mx-auto px-6 lg:px-12">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <div class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-100 rounded-full text-sm font-semibold text-emerald-700 mb-4">
          <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
          <span>Why Choose Us</span>
        </div>
        
        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">
          Why Schools
          <span class="block bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            Trust EduLink
          </span>
        </h2>
        
        <p class="text-lg text-gray-600 leading-relaxed">
          We're not just another school management system. We're your partner in transforming education across Nigeria.
        </p>
      </div>

      <!-- Benefits Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div
          v-for="(benefit, index) in benefits"
          :key="index"
          class="group relative"
        >
          <!-- Card -->
          <div class="relative h-full bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-transparent hover:border-emerald-500">
            
            <!-- Icon Badge with Gradient -->
            <div class="mb-6 relative inline-block">
              <div 
                :class="[
                  'w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-6 bg-linear-to-br',
                  benefit.gradient
                ]"
              >
                <span class="filter drop-shadow-lg">{{ benefit.icon }}</span>
              </div>
              <!-- Glow effect on hover -->
              <div 
                :class="[
                  'absolute inset-0 rounded-2xl blur-xl opacity-0 group-hover:opacity-50 transition-opacity duration-300 bg-linear-to-br',
                  benefit.gradient
                ]"
              ></div>
            </div>

            <!-- Stats -->
            <div class="mb-4">
              <div class="text-3xl font-extrabold text-gray-900 group-hover:text-emerald-600 transition-colors duration-300">
                {{ benefit.stat }}
              </div>
              <div class="text-sm font-semibold text-emerald-600">
                {{ benefit.statLabel }}
              </div>
            </div>

            <!-- Title -->
            <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-emerald-600 transition-colors duration-300">
              {{ benefit.title }}
            </h3>
            
            <!-- Description -->
            <p class="text-gray-600 text-sm leading-relaxed">
              {{ benefit.description }}
            </p>

            <!-- Decorative bottom accent -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-linear-to-r from-emerald-500 to-teal-600 rounded-b-2xl transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
          </div>
        </div>
      </div>

      <!-- Bottom Highlight Box -->
      <div class="mt-16 max-w-4xl mx-auto">
        <div class="relative bg-linear-to-r from-emerald-600 to-teal-600 rounded-2xl p-8 md:p-12 shadow-2xl overflow-hidden">
          <!-- Background pattern -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full -ml-32 -mb-32"></div>
          </div>

          <div class="relative text-center text-white">
            <h3 class="text-2xl md:text-3xl font-bold mb-4">
              Join Over 100+ Nigerian Already Using EduLink
            </h3>
            <p class="text-emerald-100 text-lg mb-6">
              From Lagos to the World, schools are transforming their operations with our platform
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
              <Link
                :href="route('register')"
                class="inline-flex items-center px-8 py-4 bg-white text-emerald-600 font-bold rounded-xl hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-xl"
              >
                <span>Register Now</span>
                <svg
                  class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-200"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
            </Link>
              <a
                href="tel:+2348012345678"
                class="inline-flex items-center px-8 py-4 bg-transparent text-white font-bold rounded-xl border-2 border-white hover:bg-white hover:text-emerald-600 transition-all duration-200"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
                <span>Call Us Now</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
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
  animation: fadeInUp 0.8s ease-out;
}
</style>