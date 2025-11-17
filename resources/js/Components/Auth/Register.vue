<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

// Current step state
const currentStep = ref(1)
const isSubmitting = ref(false)
const registrationComplete = ref(false)

// form data
const formData = ref({
  // Step 1: Role selection
  role: '',
  
  // Step 2: Personal information (common for all roles)
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  username: '',
  password: '',
  password_confirmation: '',
  agrees_to_terms: false,
  
  // Step 3: Student specific
  date_of_birth: '',
  gender: '',
  address: '',
  city: '',
  state: '',
  country: 'Nigeria',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  
  // Step 3: Parent specific
  occupation: '',
  relationship: '',
  secondary_phone: '',
  preferred_contact_method: 'email',
  receives_weekly_report: true,
  
  // Step 3: Instructor specific
  qualification: '',
  specialization: '',
  years_of_experience: '',
  linkedin_url: '',
  bio: ''
})

// validation errors
const errors = ref({})
const showPassword = ref(false)
const showConfirmPassword = ref(false);

// Role options
const roles = [
  {
    value: 'student',
    icon: '',
    title: 'Student',
    subtitle: 'Learn & Grow',
    description: 'Access learning materials, track assignments, and join virtual classes',
    benefits: ['Access course materials', 'Submit assignments', 'Track your progress', 'Join live classes']
  },
  {
    value: 'parent',
    icon: '',
    title: 'Parent',
    subtitle: 'Monitor Progress',
    description: 'Stay informed about your child\'s academic journey and performance',
    benefits: ['View child grades', 'Track attendance', 'Receive weekly reports', 'Communicate with teachers']
  },
  // {
  //   value: 'instructor',
  //   icon: '',
  //   title: 'Instructor',
  //   subtitle: 'Teach & Manage',
  //   description: 'Manage courses, grade assignments, and engage with students',
  //   benefits: ['Create courses', 'Grade assignments', 'Schedule classes', 'Track student progress']
  // }
];

//Nigerian states
const nigerianStates = [
  'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
  'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT', 'Gombe', 'Imo',
  'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa',
  'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba',
  'Yobe', 'Zamfara'
];

// Relationship options
const relationships = ['Father', 'Mother', 'Guardian', 'Other'];

// Password match validation 
const passwordsMatch = computed(() => {
    return formData.value.password && formData.value.password === formData.value.password_confirmation;
})

// Password strenght
const passwordStrength = computed(() => {
  const password = formData.value.password;
  if (!password) return { level: 0, text: '', color: '' };
  
  let strength = 0;
  if (password.length >= 8) strength++;
  if (password.length >= 12) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^a-zA-Z\d]/.test(password)) strength++;
  
  if (strength <= 2) return { level: strength, text: 'Weak', color: 'text-red-600' };
  if (strength <= 3) return { level: strength, text: 'Fair', color: 'text-orange-600' };
  if (strength <= 4) return { level: strength, text: 'Good', color: 'text-blue-600' };
  return { level: strength, text: 'Strong', color: 'text-green-600' };
})

// Step Validation
const canProceedToNextStep = computed(() => {
    if(currentStep.value === 1) {
        return formData.value.role !== '';
    }

    if (currentStep.value === 2) {
        return formData.value.first_name &&
           formData.value.last_name &&
           formData.value.email &&
           formData.value.username &&
           formData.value.password &&
           formData.value.password_confirmation &&
           passwordsMatch.value &&
           formData.value.agrees_to_terms;
    }

    if (currentStep.value === 3) {
      if (formData.value.role === 'student') {
        return formData.value.date_of_birth && formData.value.gender;
      }
      if (formData.value.role === 'parent') {
        return formData.value.relationship;
      }
      if (formData.value.role === 'instructor') {
        return formData.value.qualification;
      }
    }
  
  return false;
})

const generateUsername = () => {
  if (formData.value.email && !formData.value.username) {
    formData.value.username = formData.value.email.split('@')[0];
  }
}

// Select role
const selectRole = (roleValue) => {
  formData.value.role = roleValue;
  nextStep();
}

// Navigation
const nextStep = () => {
  if (canProceedToNextStep.value && currentStep.value < 3) {
    currentStep.value++;
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

// Submit registration
const submitRegistration = async () => {
  if (!canProceedToNextStep.value) return;
  
  isSubmitting.value = true;
  errors.value = {};
  
  try {
    await router.post('/register', formData.value, {
      preserveScroll: true,
      onSuccess: () => {
        registrationComplete.value = true;
      },
      onError: (err) => {
        errors.value = err;
        isSubmitting.value = false;
      }
    });
  } catch (error) {
    console.error('Registration error:', error);
    isSubmitting.value = false;
  }
};

// Resend verification email
const resendVerification = async () => {
  // Implement resend logic
  alert('Verification email sent!');
};

</script>

<template>
    <div class="min-h-screen bg-linear-to-b from-gray-50 to-white py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-content-between">
                <Link
                   :href="route('home')"
                   class="flex items-center space-x-3 group"
                >
                    <div class="w-12 h-12  bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-all duration-300 shadow-lg">
                        <span class="text-2xl font-bold text-white">E</span>
                    </div>
                    <span class="text-2xl font-bold bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        EduLink
                    </span>
                </Link>

                <Link
                    :href="route('home')"
                    class="text-gray-600 font-semibold transition-color flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to Home</span>
                </Link>
            </div>
        </div>
        
        <!-- Main Container -->
        <div v-if="!registrationComplete">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div v-for="step in 3" class="flex-items-center" :key="step">
                        <!-- steps -->
                        <div class="flex flex-col items-center">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all duration-300',
                                    currentStep >= step ? 'bg-linear-to-r from-emerald-500 to-teal-600 text-white shadow-lg' : 'bg-gray-300 text-gray-600'
                                ]"
                            >
                                {{ step }}
                            </div>
                            <span
                                :class="[
                                    'text-xs mt-2 font-semibold',
                                    currentStep >= step ? 'text-emerald-600' : 'text-gray-500'
                                ]"
                            >
                                {{ step === 1 ? 'Role' : step === 2 ? 'Info' : 'Details' }}
                            </span> 
                        </div>

                        <!--  -->
                        <div 
                          v-if="step < 3"
                          :class="[
                            'w-20 h-1 mx-2 rounded transition-all duration-300',
                            currentStep > step ? 'bg-linear-to-r from-emerald-500 to-teal-600' : 'bg-gray-300'
                          ]"
                        ></div>

                    </div>
                </div>
            </div>

             <!-- Step Content -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
              
              <!-- STEP 1: Role Selection -->
              <div v-if="currentStep === 1" class="animate-fade-in-up">
                <div class="text-center mb-8">
                  <h2 class="text-3xl font-bold text-gray-900 mb-2">Choose Your Role</h2>
                  <p class="text-gray-600">Select the option that best describes you</p>
                </div>
      
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                  <button
                    v-for="role in roles"
                    :key="role.value"
                    @click="selectRole(role.value)"
                    :class="[
                      'group relative p-6 rounded-2xl border-2 transition-all duration-300 text-left',
                      formData.role === role.value
                        ? 'border-emerald-500 bg-linear-to-br from-emerald-50 to-teal-50'
                        : 'border-gray-200 hover:border-emerald-500 hover:-translate-y-2 hover:shadow-xl'
                    ]"
                  >
                    <div class="text-5xl mb-4">{{ role.icon }}</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ role.title }}</h3>
                    <p class="text-sm font-semibold text-emerald-600 mb-3">{{ role.subtitle }}</p>
                    <p class="text-sm text-gray-600 mb-4">{{ role.description }}</p>
                    
                    <ul class="space-y-2">
                      <li v-for="benefit in role.benefits" :key="benefit" class="flex items-center text-xs text-gray-600">
                        <svg class="w-4 h-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ benefit }}
                      </li>
                    </ul>
      
                    <div class="mt-6 flex items-center justify-center px-4 py-2 bg-emerald-500 text-white rounded-lg group-hover:bg-emerald-600 transition-colors">
                      Select
                    </div>
                  </button>
                </div>
              </div>
      
              <!-- STEP 2: Personal Information -->
              <div v-if="currentStep === 2" class="animate-fade-in-up">
                <div class="text-center mb-8">
                  <h2 class="text-3xl font-bold text-gray-900 mb-2">Personal Information</h2>
                  <p class="text-gray-600">Let's get to know you better</p>
                </div>
      
                <div class="space-y-6">
                  <!-- Name -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                      <input
                        v-model="formData.first_name"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="John"
                      />
                      <p v-if="errors.first_name" class="text-red-600 text-xs mt-1">{{ errors.first_name }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                      <input
                        v-model="formData.last_name"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="Doe"
                      />
                      <p v-if="errors.last_name" class="text-red-600 text-xs mt-1">{{ errors.last_name }}</p>
                    </div>
                  </div>
      
                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                    <input
                      v-model="formData.email"
                      @blur="generateUsername"
                      type="email"
                      required
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="john.doe@example.com"
                    />
                    <p class="text-xs text-gray-500 mt-1">This will be your login username</p>
                    <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</p>
                  </div>
      
                  <!-- Phone & Username -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                      <input
                        v-model="formData.phone"
                        type="tel"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="+234 800 000 0000"
                      />
                      <p v-if="errors.phone" class="text-red-600 text-xs mt-1">{{ errors.phone }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                      <input
                        v-model="formData.username"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="johndoe"
                      />
                      <p v-if="errors.username" class="text-red-600 text-xs mt-1">{{ errors.username }}</p>
                    </div>
                  </div>
      
                  <!-- Password -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                      <div class="relative">
                        <input
                          v-model="formData.password"
                          :type="showPassword ? 'text' : 'password'"
                          required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all pr-10"
                          placeholder="••••••••"
                        />
                        <button
                          @click="showPassword = !showPassword"
                          type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                          <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          </svg>
                        </button>
                      </div>
                      <p class="text-xs mt-1" :class="passwordStrength.color">
                        {{ formData.password ? `Strength: ${passwordStrength.text}` : 'Min 8 characters' }}
                      </p>
                      <p v-if="errors.password" class="text-red-600 text-xs mt-1">{{ errors.password }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password *</label>
                      <div class="relative">
                        <input
                          v-model="formData.password_confirmation"
                          :type="showConfirmPassword ? 'text' : 'password'"
                          required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all pr-10"
                          placeholder="••••••••"
                        />
                        <button
                          @click="showConfirmPassword = !showConfirmPassword"
                          type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                          <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          </svg>
                        </button>
                      </div>
                      <p v-if="passwordsMatch && formData.password_confirmation" class="text-green-600 text-xs mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Passwords match
                      </p>
                      <p v-else-if="formData.password_confirmation && !passwordsMatch" class="text-red-600 text-xs mt-1">Passwords don't match</p>
                    </div>
                  </div>
      
                  <!-- Terms & Conditions -->
                  <div class="flex items-start">
                    <input
                      v-model="formData.agrees_to_terms"
                      type="checkbox"
                      id="terms"
                      class="mt-1 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                    />
                    <label for="terms" class="ml-3 text-sm text-gray-700">
                      I agree to the <a href="/terms" class="text-emerald-600 hover:underline">Terms of Service</a> and <a href="/privacy" class="text-emerald-600 hover:underline">Privacy Policy</a>
                    </label>
                  </div>
                  <p v-if="errors.agrees_to_terms" class="text-red-600 text-xs">{{ errors.agrees_to_terms }}</p>
                </div>
              </div>
      
              <!-- STEP 3: Additional Details -->
              <div v-if="currentStep === 3" class="animate-fade-in-up">
                <div class="text-center mb-8">
                  <h2 class="text-3xl font-bold text-gray-900 mb-2">Complete Your Profile</h2>
                  <p class="text-gray-600">Just a few more details to get started</p>
                </div>
      
                <!-- Student Fields -->
                <div v-if="formData.role === 'student'" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth *</label>
                      <input
                        v-model="formData.date_of_birth"
                        type="date"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      />
                      <p v-if="errors.date_of_birth" class="text-red-600 text-xs mt-1">{{ errors.date_of_birth }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                      <div class="flex items-center space-x-6 mt-3">
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.gender" type="radio" value="male" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">Male</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.gender" type="radio" value="female" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">Female</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.gender" type="radio" value="other" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">Other</span>
                        </label>
                      </div>
                      <p v-if="errors.gender" class="text-red-600 text-xs mt-1">{{ errors.gender }}</p>
                    </div>
                  </div>
      
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <input
                      v-model="formData.address"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="Street address"
                    />
                  </div>
      
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                      <input
                        v-model="formData.city"
                        type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="Lagos"
                      />
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                      <select
                        v-model="formData.state"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      >
                        <option value="">Select State</option>
                        <option v-for="state in nigerianStates" :key="state" :value="state">{{ state }}</option>
                      </select>
                    </div>
                  </div>
      
                  <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Name</label>
                        <input
                          v-model="formData.emergency_contact_name"
                          type="text"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                          placeholder="Parent/Guardian name"
                        />
                      </div>
      
                      <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Phone</label>
                        <input
                          v-model="formData.emergency_contact_phone"
                          type="tel"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                          placeholder="+234 800 000 0000"
                        />
                      </div>
                    </div>
                  </div>
                </div>
      
                <!-- Parent Fields -->
                <div v-if="formData.role === 'parent'" class="space-y-6">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Occupation</label>
                    <input
                      v-model="formData.occupation"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="e.g., Engineer, Teacher, Business Owner"
                    />
                  </div>
      
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Relationship *</label>
                      <select
                        v-model="formData.relationship"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      >
                        <option value="">Select Relationship</option>
                        <option v-for="rel in relationships" :key="rel" :value="rel.toLowerCase()">{{ rel }}</option>
                      </select>
                      <p v-if="errors.relationship" class="text-red-600 text-xs mt-1">{{ errors.relationship }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Phone</label>
                      <input
                        v-model="formData.secondary_phone"
                        type="tel"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="+234 800 000 0000"
                      />
                    </div>
                  </div>
      
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <input
                      v-model="formData.address"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="Street address"
                    />
                  </div>
      
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                      <input
                        v-model="formData.city"
                        type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="Lagos"
                      />
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                      <select
                        v-model="formData.state"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      >
                        <option value="">Select State</option>
                        <option v-for="state in nigerianStates" :key="state" :value="state">{{ state }}</option>
                      </select>
                    </div>
                  </div>
      
                  <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Communication Preferences</h3>
                    
                    <div class="mb-4">
                      <label class="block text-sm font-semibold text-gray-700 mb-3">Preferred Contact Method</label>
                      <div class="flex items-center space-x-6">
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.preferred_contact_method" type="radio" value="email" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">Email</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.preferred_contact_method" type="radio" value="phone" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">Phone</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                          <input v-model="formData.preferred_contact_method" type="radio" value="sms" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"/>
                          <span class="ml-2 text-gray-700">SMS</span>
                        </label>
                      </div>
                    </div>
      
                    <div class="flex items-start">
                      <input
                        v-model="formData.receives_weekly_report"
                        type="checkbox"
                        id="weekly-report"
                        class="mt-1 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                      />
                      <label for="weekly-report" class="ml-3 text-sm text-gray-700">
                        Receive weekly progress reports about my child's performance
                      </label>
                    </div>
                  </div>
                </div>
      
                <!-- Instructor Fields -->
                <div v-if="formData.role === 'instructor'" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Qualification *</label>
                      <input
                        v-model="formData.qualification"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="e.g., B.Ed, M.Sc, Ph.D"
                      />
                      <p v-if="errors.qualification" class="text-red-600 text-xs mt-1">{{ errors.qualification }}</p>
                    </div>
      
                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Specialization</label>
                      <input
                        v-model="formData.specialization"
                        type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        placeholder="e.g., Mathematics, Physics, English"
                      />
                    </div>
                  </div>
      
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Years of Experience</label>
                    <input
                      v-model="formData.years_of_experience"
                      type="number"
                      min="0"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="e.g., 5"
                    />
                  </div>
      
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">LinkedIn Profile URL</label>
                    <input
                      v-model="formData.linkedin_url"
                      type="url"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                      placeholder="https://linkedin.com/in/yourprofile"
                    />
                  </div>
      
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Professional Bio</label>
                    <textarea
                      v-model="formData.bio"
                      rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none"
                      placeholder="Tell us about your teaching experience, expertise, and what makes you passionate about education..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">This will be displayed on your instructor profile</p>
                  </div>
                </div>
              </div>
      
              <!-- Navigation Buttons -->
              <div class="flex items-center justify-between mt-8 pt-6 border-t">
                <button
                  v-if="currentStep > 1"
                  @click="previousStep"
                  type="button"
                  class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                  </svg>
                  Previous
                </button>
                <div v-else></div>
      
                <button
                  v-if="currentStep < 3"
                  @click="nextStep"
                  :disabled="!canProceedToNextStep"
                  type="button"
                  :class="[
                    'inline-flex items-center px-6 py-3 font-semibold rounded-lg transition-all duration-200',
                    canProceedToNextStep
                      ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 shadow-lg'
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]"
                >
                  Next Step
                  <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
      
                <button
                  v-else
                  @click="submitRegistration"
                  :disabled="!canProceedToNextStep || isSubmitting"
                  type="button"
                  :class="[
                    'inline-flex items-center px-8 py-3 font-semibold rounded-lg transition-all duration-200',
                    canProceedToNextStep && !isSubmitting
                      ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 shadow-lg'
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]"
                >
                  <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSubmitting ? 'Creating Account...' : 'Complete Registration' }}
                </button>
              </div>
            </div>
        </div>

    <!-- Success Screen -->
    <div v-else class="max-w-2xl mx-auto">
      <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
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
          <p class="font-semibold text-emerald-600">{{ formData.email }}</p>
        </div>

        <p class="text-gray-600 mb-8">
          Please check your inbox and verify your email address to activate your account.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <a
            href="/login"
            class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-lg"
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
            class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm hover:underline"
          >
            Resend Verification Email
          </button>
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