<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

const showVideoModal = ref(false);
</script>

<template>
  <section
    class="relative min-h-screen flex items-center overflow-hidden bg-linear-to-br from-green-700 via-emerald-600 to-green-500 text-white"
  >
    <!-- Overlay for contrast -->
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-1"></div>

    <!-- Background floating visuals -->
    <div class="absolute inset-0 overflow-hidden z-0">
      <div
        class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"
      ></div>
      <div
        class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-float-delayed"
      ></div>
      <div
        class="absolute top-1/2 left-1/3 w-64 h-64 bg-white/5 rounded-full blur-2xl animate-pulse"
      ></div>
      <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
    </div>

    <!-- Content -->
    <div
      class="relative z-10 container mx-auto px-6 lg:px-12 py-32 grid lg:grid-cols-2 gap-16 items-center"
    >
      <!-- Left: Text -->
      <div class="space-y-8 animate-fade-in-up">
        <div
          class="inline-flex items-center space-x-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold"
        >
          <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
          <span>Transforming Education in Nigeria</span>
        </div>

        <h1
          class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-tight drop-shadow-[0_2px_4px_rgba(0,0,0,0.25)]"
        >
          Modern School
          <span
            class="block bg-linear-to-r from-green-100 to-white bg-clip-text text-transparent"
            >Management</span
          >
          Made Simple
        </h1>

        <p class="text-xl text-white/90 font-medium">
          A complete platform for students, parents, instructors, and
          administrators
        </p>

        <p class="text-lg text-white/80 leading-relaxed max-w-xl">
          Streamline your school operations with our all-in-one learning
          management system. Track attendance, manage courses, grade
          assignments, and keep parents informed—all in one place.
        </p>

        <!-- Features -->
        <div class="grid grid-cols-2 gap-4 pt-4">
          <div class="flex items-center space-x-2">
            <svg
              class="w-6 h-6 text-green-300"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span>Easy to Use</span>
          </div>
          <div class="flex items-center space-x-2">
            <svg
              class="w-6 h-6 text-green-300"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span>Cloud Based</span>
          </div>
          <div class="flex items-center space-x-2">
            <svg
              class="w-6 h-6 text-green-300"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span>Real-time Updates</span>
          </div>
          <div class="flex items-center space-x-2">
            <svg
              class="w-6 h-6 text-green-300"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span>Mobile Friendly</span>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
          <Link
            :href="route('register')"
            class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-primary-600 bg-green-300 rounded-xl hover:bg-green-300 transform hover:scale-105 transition-all duration-200 shadow-xl hover:shadow-2xl"
          >
            <span>Get Started Free</span>
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

          <button
            @click="showVideoModal = true"
            class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 backdrop-blur-sm transition-all duration-200"
          >
            <svg
              class="w-6 h-6 mr-2"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
              />
            </svg>
            <span>Watch Demo</span>
          </button>
        </div>

        <!-- Trust stats -->
        <div class="flex items-center space-x-6 pt-8 border-t border-white/20">
          <div class="text-center">
            <div class="text-3xl font-bold">500+</div>
            <div class="text-sm text-white/70">Active Students</div>
          </div>
          <div class="h-12 w-px bg-white/20"></div>
          <div class="text-center">
            <div class="text-3xl font-bold">50+</div>
            <div class="text-sm text-white/70">Courses</div>
          </div>
          <div class="h-12 w-px bg-white/20"></div>
          <div class="text-center">
            <div class="text-3xl font-bold">95%</div>
            <div class="text-sm text-white/70">Success Rate</div>
          </div>
        </div>
      </div>

      <!-- Right: Floating Cards -->
      <div class="relative animate-fade-in-right hidden lg:block">
        <div
          class="absolute top-0 right-0 w-72 bg-white rounded-2xl shadow-2xl p-6 animate-float"
        >
          <h3 class="font-semibold text-gray-900 mb-2">Student Progress</h3>
          <div class="space-y-3">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Mathematics</span><span class="font-semibold text-green-600"
                >92%</span
              >
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-green-500 h-2 rounded-full"
                style="width: 92%"
              ></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span>English</span><span class="font-semibold text-blue-600"
                >85%</span
              >
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-blue-500 h-2 rounded-full"
                style="width: 85%"
              ></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span>Science</span><span class="font-semibold text-purple-600"
                >88%</span
              >
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-purple-500 h-2 rounded-full"
                style="width: 88%"
              ></div>
            </div>
          </div>
        </div>

        <div
          class="absolute top-48 left-0 w-64 bg-white rounded-2xl shadow-2xl p-5 animate-float-delayed"
        >
          <h3 class="font-semibold text-gray-900 mb-3">Today's Classes</h3>
          <div class="space-y-3">
            <div class="p-2 bg-green-50 rounded-lg">
              <p class="text-sm font-medium text-gray-900">Physics Lab</p>
              <p class="text-xs text-gray-500">10:00 AM – 11:30 AM</p>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg">
              <p class="text-sm font-medium text-gray-900">History</p>
              <p class="text-xs text-gray-500">1:00 PM – 2:30 PM</p>
            </div>
          </div>
        </div>

        <div
          class="absolute bottom-0 right-12 w-56 bg-linear-to-br from-green-500 to-green-600 rounded-2xl shadow-2xl p-5 text-white animate-float"
        >
          <p class="font-medium mb-2">New Grade! Assignment submitted</p>
          <div class="bg-white/20 rounded-lg p-3">
            <div class="flex justify-between">
              <span>Mathematics Quiz</span>
              <span class="text-2xl font-bold">A+</span>
            </div>
            <p class="text-xs opacity-80 mt-1">Score: 95 / 100</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div
      class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce z-10"
    >
      <a
        href="#features"
        class="flex flex-col items-center space-y-2 text-white/60 hover:text-white transition"
      >
        <span class="text-sm">Scroll to explore</span>
        <svg
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 14l-7 7m0 0l-7-7m7 7V3"
          />
        </svg>
      </a>
    </div>

    <!-- Video Modal -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showVideoModal"
        @click="showVideoModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm px-4"
      >
        <div
          @click.stop
          class="relative w-full max-w-4xl bg-white rounded-2xl overflow-hidden shadow-2xl"
        >
          <button
            @click="showVideoModal = false"
            class="absolute top-4 right-4 p-2 bg-white rounded-full hover:bg-gray-100"
          >
            <svg
              class="w-6 h-6 text-gray-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <!-- YouTube video -->
          <div class="aspect-video">
            <iframe
              class="w-full h-full"
              src="https://www.youtube.com/embed/ysz5S6PUM-U?autoplay=1"
              title="Demo Video"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<style scoped>
.bg-grid-pattern {
  background-image: linear-gradient(
      to right,
      rgba(255, 255, 255, 0.1) 1px,
      transparent 1px
    ),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 40px 40px;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-15px);
  }
}
@keyframes float-delayed {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
.animate-float {
  animation: float 6s ease-in-out infinite;
}
.animate-float-delayed {
  animation: float-delayed 8s ease-in-out infinite;
  animation-delay: 1s;
}
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes fade-in-right {
  from {
    opacity: 0;
    transform: translateX(40px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out;
}
.animate-fade-in-right {
  animation: fade-in-right 0.8s ease-out 0.3s both;
}
</style>
