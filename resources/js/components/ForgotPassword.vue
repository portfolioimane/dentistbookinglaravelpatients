<template>
  <div class="container mt-5">
    <h1 class="text-center">Forgot Password</h1>
    <form @submit.prevent="sendResetLink">
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" id="email" v-model="email" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>
    
    <div v-if="message" class="mt-3 alert alert-success">{{ message }}</div>
    <div v-if="error" class="mt-3 alert alert-danger">{{ error }}</div>
    
    <p class="mt-3 text-center">
      Remembered your password? <router-link to="/login">Login here</router-link>
    </p>
  </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
  data() {
    return {
      email: '',
      message: null,
      error: null,
    };
  },
  methods: {
    ...mapActions('auth', ['sendPasswordResetLink']),  // Map action to send password reset

    async sendResetLink() {
      try {
        await this.sendPasswordResetLink({ email: this.email });
        this.message = 'If this email exists, we have sent a reset link to your email address.';
        this.error = null;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to send reset link. Please try again.';
        this.message = null;
      }
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 500px; /* Limit the width of the form */
}
</style>
