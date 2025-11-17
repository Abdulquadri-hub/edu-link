<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js'

const features = [
  {
    icon: '👨‍🎓',
    title: 'Student Management',
    description: 'Comprehensive student profiles, enrollment tracking, and academic records all in one place.',
    color: 'from-blue-500 to-blue-600',
    hoverColor: 'group-hover:from-blue-600 group-hover:to-blue-700'
  },
  {
    icon: '📖',
    title: 'Course Management',
    description: 'Create, organize, and manage courses with flexible curriculum planning tools.',
    color: 'from-purple-500 to-purple-600',
    hoverColor: 'group-hover:from-purple-600 group-hover:to-purple-700'
  },
  {
    icon: '✍️',
    title: 'Assignment & Grading',
    description: 'Streamline assignment creation, submission tracking, and automated grading systems.',
    color: 'from-pink-500 to-pink-600',
    hoverColor: 'group-hover:from-pink-600 group-hover:to-pink-700'
  },
  {
    icon: '📅',
    title: 'Class Schedules',
    description: 'Intelligent timetable management with conflict detection and automatic notifications.',
    color: 'from-orange-500 to-orange-600',
    hoverColor: 'group-hover:from-orange-600 group-hover:to-orange-700'
  },
  {
    icon: '✓',
    title: 'Attendance Tracking',
    description: 'Real-time attendance monitoring with automated reports and parent notifications.',
    color: 'from-emerald-500 to-emerald-600',
    hoverColor: 'group-hover:from-emerald-600 group-hover:to-emerald-700'
  },
  {
    icon: '👪',
    title: 'Parent Portal',
    description: 'Keep parents engaged with instant access to grades, attendance, and school updates.',
    color: 'from-teal-500 to-teal-600',
    hoverColor: 'group-hover:from-teal-600 group-hover:to-teal-700'
  },
  {
    icon: '📊',
    title: 'Reports & Analytics',
    description: 'Data-driven insights with customizable dashboards and comprehensive performance metrics.',
    color: 'from-indigo-500 to-indigo-600',
    hoverColor: 'group-hover:from-indigo-600 group-hover:to-indigo-700'
  },
  {
    icon: '📚',
    title: 'Learning Materials',
    description: 'Centralized digital library for course materials, resources, and multimedia content.',
    color: 'from-cyan-500 to-cyan-600',
    hoverColor: 'group-hover:from-cyan-600 group-hover:to-cyan-700'
  }
];
</script>

<template>
  <section id="features" class="relative py-24 bg-linear-to-b from-white to-gray-50 overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 right-10 w-96 h-96 bg-emerald-100 rounded-full blur-3xl opacity-30"></div>
      <div class="absolute bottom-20 left-10 w-96 h-96 bg-teal-100 rounded-full blur-3xl opacity-30"></div>
    </div>

    <div class="relative container mx-auto px-6 lg:px-12">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <div class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-100 rounded-full text-sm font-semibold text-emerald-700 mb-4">
          <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
          <span>Powerful Features</span>
        </div>
        
        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">
          Everything You Need to
          <span class="block bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            Manage Your School
          </span>
        </h2>
        
        <p class="text-lg text-gray-600 leading-relaxed">
          Comprehensive tools designed specifically for Nigerian schools. From student management to parent engagement, we've got you covered.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(feature, index) in features"
          :key="index"
          class="group relative bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
        >
          <!-- Icon Container -->
          <div class="mb-4">
            <div 
              :class="[
                'w-16 h-16 rounded-xl flex items-center justify-center text-3xl transition-all duration-300 bg-linear-to-br',
                feature.color,
                feature.hoverColor
              ]"
            >
              <span class="transform group-hover:scale-110 transition-transform duration-300">
                {{ feature.icon }}
              </span>
            </div>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-emerald-600 transition-colors duration-300">
            {{ feature.title }}
          </h3>
          
          <p class="text-gray-600 text-sm leading-relaxed">
            {{ feature.description }}
          </p>

          <!-- Hover Border Effect -->
          <div class="absolute inset-0 rounded-2xl border-2 border-transparent group-hover:border-emerald-500 transition-all duration-300"></div>

          <!-- Decorative Corner -->
          <div class="absolute top-0 right-0 w-20 h-20 bg-linear-to-br from-emerald-500/10 to-transparent rounded-tr-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
      </div>

      <!-- Bottom CTA -->
      <div class="text-center mt-16 animate-fade-in-up">
        <p class="text-gray-600 mb-6">
          Ready to transform your school management?
        </p>
        <Link
          :href="route('register')"
          class="inline-flex items-center px-8 py-4 bg-linear-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-xl hover:shadow-2xl"
        >
          <span>Get Started Today</span>
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
              d="M13 7l5 5m0 0l-5 5m5-5H6"
            />
          </svg>
        </Link>
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