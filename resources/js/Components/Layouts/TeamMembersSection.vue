<script setup>
import { Link } from '@inertiajs/vue3';

// Props for dynamic data from backend (optional)
const props = defineProps({
  teamMembers: {
    type: Array,
    default: () => []
  }
});

// Default team data (fallback when no database data)
const defaultTeam = [
  {
    id: 1,
    name: 'Dr. Adewale Johnson',
    role: 'Founder & CEO',
    avatar: 'https://ui-avatars.com/api/?name=Adewale+Johnson&size=400&background=10b981&color=fff&bold=true',
    bio: 'Former Principal with 15+ years experience in Nigerian education system',
    specialization: 'Educational Leadership'
  },
  {
    id: 2,
    name: 'Mrs. Blessing Okafor',
    role: 'Head of Academics',
    avatar: 'https://ui-avatars.com/api/?name=Blessing+Okafor&size=400&background=0ea5e9&color=fff&bold=true',
    bio: 'Mathematics expert and curriculum developer',
    specialization: 'Curriculum Development'
  },
  {
    id: 3,
    name: 'Mr. Ibrahim Musa',
    role: 'Lead Instructor',
    avatar: 'https://ui-avatars.com/api/?name=Ibrahim+Musa&size=400&background=8b5cf6&color=fff&bold=true',
    bio: 'Science and Technology educator passionate about innovation',
    specialization: 'STEM Education'
  },
  {
    id: 4,
    name: 'Miss Chioma Nwosu',
    role: 'Student Success Manager',
    avatar: 'https://ui-avatars.com/api/?name=Chioma+Nwosu&size=400&background=f59e0b&color=fff&bold=true',
    bio: 'Dedicated to ensuring every student reaches their full potential',
    specialization: 'Student Welfare'
  }
];

// Use database data if available, otherwise use default
const displayTeam = props.teamMembers.length > 0 ? props.teamMembers : defaultTeam;

</script>

<template>
  <section class="relative py-24 bg-white overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/2 left-0 w-72 h-72 bg-emerald-100 rounded-full blur-3xl opacity-30 -translate-x-1/2"></div>
      <div class="absolute top-1/2 right-0 w-72 h-72 bg-teal-100 rounded-full blur-3xl opacity-30 translate-x-1/2"></div>
    </div>

    <div class="relative container mx-auto px-6 lg:px-12">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <div class="inline-flex items-center space-x-2 px-4 py-2 bg-emerald-100 rounded-full text-sm font-semibold text-emerald-700 mb-4">
          <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
          <span>Meet Our Team</span>
        </div>
        
        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4">
          Experts Dedicated to
          <span class="block bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            Your Success
          </span>
        </h2>
        
        <p class="text-lg text-gray-600 leading-relaxed">
          Our passionate team of educators and technologists are committed to transforming Nigerian education.
        </p>
      </div>

      <!-- Team Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <div
          v-for="member in displayTeam"
          :key="member.id"
          class="group relative"
        >
          <!-- Card -->
          <div class="relative bg-gradient-to-b from-gray-50 to-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
            
            <!-- Avatar -->
            <div class="relative mb-4">
              <div class="w-full aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 p-1">
                <img 
                  :src="member.avatar" 
                  :alt="member.name"
                  class="w-full h-full object-cover rounded-lg transform group-hover:scale-105 transition-transform duration-300"
                />
              </div>
              <!-- Online badge -->
              <div class="absolute bottom-2 right-2 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>

            <!-- Info -->
            <div class="text-center">
              <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-emerald-600 transition-colors duration-300">
                {{ member.name }}
              </h3>
              
              <p class="text-sm font-semibold text-emerald-600 mb-2">
                {{ member.role }}
              </p>
              
              <p class="text-xs text-gray-600 mb-3 line-clamp-2">
                {{ member.bio }}
              </p>

              <!-- Specialization tag -->
              <div class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-medium">
                {{ member.specialization }}
              </div>
            </div>

            <!-- Hover overlay with social/contact -->
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-600 to-transparent opacity-0 group-hover:opacity-95 transition-opacity duration-300 rounded-2xl flex items-end justify-center p-6">
              <div class="text-center text-white transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                <p class="text-sm mb-3">{{ member.bio }}</p>
                <div class="flex items-center justify-center space-x-3">
                  <button class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                  </button>
                  <button class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- View Full Team CTA -->
      <div class="text-center">
        <Link
          href="/about"
          class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-xl hover:shadow-2xl"
        >
          <span>Meet Our Full Team</span>
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
      </div>

      <!-- Trust indicator -->
      <div class="mt-12 flex items-center justify-center space-x-8 opacity-70">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">15+</div>
          <div class="text-sm text-gray-600">Years Experience</div>
        </div>
        <div class="h-12 w-px bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">50+</div>
          <div class="text-sm text-gray-600">Expert Instructors</div>
        </div>
        <div class="h-12 w-px bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">100%</div>
          <div class="text-sm text-gray-600">Dedicated to Excellence</div>
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>