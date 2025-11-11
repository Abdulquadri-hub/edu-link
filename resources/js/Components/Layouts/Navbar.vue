<template>
  <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <div class="flex-shrink-0">
          <a href="#" class="flex items-center space-x-3 group">
            <div
              class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-all duration-300 shadow-lg"
            >
              <span class="text-2xl font-bold text-white">E</span>
            </div>
            <span
              class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent"
            >
              EduLink
            </span>
          </a>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex lg:items-center lg:space-x-1">
          <!-- Features Dropdown -->
          <div
            class="relative"
            @mouseenter="activeDropdown = 'features'"
            @mouseleave="activeDropdown = null"
          >
            <button
              class="flex items-center space-x-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200"
            >
              <span>Features</span>
              <svg
                :class="[
                  'w-4 h-4 transition-transform duration-200',
                  activeDropdown === 'features' ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <div
              v-if="activeDropdown === 'features'"
              class="absolute left-0 mt-2 w-[420px] bg-white rounded-xl shadow-2xl border border-gray-100"
            >
              <div class="p-6">
                <h3
                  class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"
                >
                  School Management Features
                </h3>
                <div class="grid grid-cols-2 gap-2">
                  <a
                    v-for="feature in schoolFeatures"
                    :key="feature.name"
                    href="#features"
                    class="flex items-start space-x-3 p-3 rounded-lg hover:bg-emerald-50 transition-colors duration-200 group"
                  >
                    <div class="flex-shrink-0 text-2xl">{{ feature.icon }}</div>
                    <div class="flex-1 min-w-0">
                      <p
                        class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 truncate"
                      >
                        {{ feature.name }}
                      </p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        {{ feature.description }}
                      </p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Our Systems Dropdown -->
          <div
            class="relative"
            @mouseenter="activeDropdown = 'systems'"
            @mouseleave="activeDropdown = null"
          >
            <button
              class="flex items-center space-x-1 px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200"
            >
              <span>Our Systems</span>
              <svg
                :class="[
                  'w-4 h-4 transition-transform duration-200',
                  activeDropdown === 'systems' ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <div
              v-if="activeDropdown === 'systems'"
              class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100"
            >
              <div class="p-6">
                <h3
                  class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"
                >
                  Other Business Solutions
                </h3>
                <div class="space-y-1">
                  <a
                    v-for="system in otherSystems"
                    :key="system.name"
                    href="#"
                    class="flex items-start space-x-3 p-3 rounded-lg hover:bg-emerald-50 transition-colors duration-200 group"
                  >
                    <div class="text-2xl">{{ system.icon }}</div>
                    <div>
                      <p
                        class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600"
                      >
                        {{ system.name }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1">
                        {{ system.description }}
                      </p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Regular Links -->
          <a
            href="#about"
            class="px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200"
          >
            About
          </a>
          <a
            href="#contact"
            class="px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200"
          >
            Contact
          </a>
        </div>

        <!-- Right Side Actions -->
        <div class="hidden lg:flex lg:items-center lg:space-x-3">
          <!-- Login Dropdown -->
          <div
            class="relative"
            @mouseenter="activeDropdown = 'login'"
            @mouseleave="activeDropdown = null"
          >
            <button
              class="flex items-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 border-2 border-gray-200 hover:border-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-200"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              <span>Login</span>
              <svg
                :class="[
                  'w-4 h-4 transition-transform duration-200',
                  activeDropdown === 'login' ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <div
              v-if="activeDropdown === 'login'"
              class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100"
            >
              <div class="p-3">
                <a
                  v-for="role in loginRoles"
                  :key="role.type"
                  :href="role.url"
                  class="flex items-center space-x-3 px-4 py-3 hover:bg-emerald-50 rounded-lg transition-colors duration-200 group"
                >
                  <div
                    class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-500 group-hover:scale-110 transition-all duration-200"
                  >
                    <span class="text-xl">{{ role.icon }}</span>
                  </div>
                  <div>
                    <p
                      class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600"
                    >
                      {{ role.name }}
                    </p>
                    <p class="text-xs text-gray-500">{{ role.subtitle }}</p>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <!-- Register Button -->
          <Link
            :href="route('register')"
            class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            Register Free
        </Link>
        </div>

        <!-- Mobile Menu Button -->
        <div class="lg:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 transition-all duration-200"
          >
            <svg
              v-if="!mobileMenuOpen"
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
            <svg
              v-else
              class="w-6 h-6"
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
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div
      v-if="mobileMenuOpen"
      class="lg:hidden bg-white border-t border-gray-200 shadow-lg"
    >
      <div
        class="px-4 py-6 space-y-4 max-h-[80vh] overflow-y-auto"
      >
        <!-- Features (Mobile) -->
        <div>
          <button
            @click="toggleMobileSection('features')"
            class="flex items-center justify-between w-full px-4 py-3 text-left font-semibold text-gray-900 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
          >
            <span>Features</span>
            <svg
              :class="[
                'w-5 h-5 transition-transform duration-200',
                mobileSectionOpen === 'features' ? 'rotate-180' : '',
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </button>

          <div
            v-if="mobileSectionOpen === 'features'"
            class="mt-2 ml-4 space-y-2"
          >
            <a
              v-for="feature in schoolFeatures"
              :key="feature.name"
              href="#features"
              class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
            >
              <span>{{ feature.icon }}</span>
              <span>{{ feature.name }}</span>
            </a>
          </div>
        </div>

        <!-- Systems (Mobile) -->
        <div>
          <button
            @click="toggleMobileSection('systems')"
            class="flex items-center justify-between w-full px-4 py-3 text-left font-semibold text-gray-900 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
          >
            <span>Our Systems</span>
            <svg
              :class="[
                'w-5 h-5 transition-transform duration-200',
                mobileSectionOpen === 'systems' ? 'rotate-180' : '',
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </button>

          <div
            v-if="mobileSectionOpen === 'systems'"
            class="mt-2 ml-4 space-y-2"
          >
            <a
              v-for="system in otherSystems"
              :key="system.name"
              href="#"
              class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
            >
              <span>{{ system.icon }}</span>
              <span>{{ system.name }}</span>
            </a>
          </div>
        </div>

        <!-- Regular Links (Mobile) -->
        <a
          href="#about"
          class="block px-4 py-3 font-semibold text-gray-900 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
        >
          About
        </a>
        <a
          href="#contact"
          class="block px-4 py-3 font-semibold text-gray-900 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
        >
          Contact
        </a>

        <!-- Login Roles (Mobile) -->
        <div class="pt-4 border-t-2 border-gray-200">
          <p
            class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-3"
          >
            Login As
          </p>
          <div class="space-y-2">
            <a
              v-for="role in loginRoles"
              :key="role.type"
              href="#"
              class="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-200"
            >
              <div
                class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center"
              >
                <span class="text-lg">{{ role.icon }}</span>
              </div>
              <div>
                <p class="font-semibold">{{ role.name }}</p>
                <p class="text-xs text-gray-500">{{ role.subtitle }}</p>
              </div>
            </a>
          </div>
        </div>

        <!-- Register (Mobile) -->
        <Link
          :href="route('register')"
          class="block w-full px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-center font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg"
        >
          Register Free →
        </Link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { route } from 'ziggy-js'

// Reactive states
const activeDropdown = ref(null)
const mobileMenuOpen = ref(false)
const mobileSectionOpen = ref(null)

// Dropdown togglers
const toggleMobileSection = (section) => {
  mobileSectionOpen.value =
    mobileSectionOpen.value === section ? null : section
}

// Data
const schoolFeatures = [
  { name: 'Student Management', description: 'Complete student records and profiles', icon: '👨‍🎓' },
  { name: 'Course Management', description: 'Organize courses and curricula', icon: '📖' },
  { name: 'Assignment & Grading', description: 'Create and grade assignments easily', icon: '✍️' },
  { name: 'Class Schedules', description: 'Schedule and manage classes', icon: '📅' },
  { name: 'Attendance Tracking', description: 'Monitor student attendance', icon: '✓' },
  { name: 'Parent Portal', description: 'Keep parents informed and engaged', icon: '👪' },
  { name: 'Reports & Analytics', description: 'Comprehensive performance insights', icon: '📊' },
  { name: 'Learning Materials', description: 'Centralized resource library', icon: '📚' },
]

const otherSystems = [
  { name: 'Fashion & Food Marketplace', description: 'E-commerce for fashion and foodstuffs', icon: '🛍️' },
  { name: 'Donation Platform', description: 'Support orphanages and communities', icon: '❤️' },
  { name: 'Bible Study & Growth', description: 'Daily devotionals and spiritual growth', icon: '📖' },
  { name: 'Delivery Services', description: 'Lagos and Ibadan delivery tracking', icon: '🚚' },
]
const loginRoles = [
{
    type: 'admin',
    name: 'Admin',
    subtitle: 'School management',
    icon: '👨‍💼',
    url: '/admin/login'
},
{
    type: 'instructor',
    name: 'Instructor',
    subtitle: 'Teaching dashboard',
    icon: '👨‍🏫',
    url: '/instructor/login'
},
{
    type: 'student',
    name: 'Student',
    subtitle: 'Learning portal',
    icon: '👨‍🎓',
    url: '/student/login'
},
{
    type: 'parent',
    name: 'Parent',
    subtitle: 'Monitor progress',
    icon: '👨‍👩‍👧',
    url: '/parent/login'
},
]

</script>
