<template>
  <div class="reset-password-container">
    <div class="form-card">
      <h2 class="form-title">Reset Your Password</h2>
      <form @submit.prevent="resetPassword" class="reset-password-form">
        <div class="input-group">
          <label for="email" class="input-label">Email</label>
          <input
            type="email"
            id="email"
            v-model="email"
            required
            class="input-field"
          />
        </div>

        <div class="input-group">
          <label for="password" class="input-label">New Password</label>
          <input
            type="password"
            id="password"
            v-model="password"
            required
            minlength="8"
            class="input-field"
          />
        </div>

        <div class="input-group">
          <label for="password_confirmation" class="input-label">Confirm Password</label>
          <input
            type="password"
            id="password_confirmation"
            v-model="password_confirmation"
            required
            class="input-field"
          />
        </div>

        <button type="submit" class="submit-btn">Reset Password</button>

        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
  data() {
    return {
      email: '',
      password: '',
      password_confirmation: '',
      token: this.$route.params.token, // Accessing token from route params
      errorMessage: '',
    };
  },
  methods: {
    async resetPassword() {
      // Check if passwords match
      if (this.password !== this.password_confirmation) {
        this.errorMessage = 'Passwords do not match.';
        return;
      }

      // Log the token value for debugging
      console.log('Reset Password Token:', this.token);  // This will log the token to the browser console

      try {
        // Dispatch the Vuex action to reset the password
        await this.$store.dispatch('auth/resetPassword', {
          email: this.email,
          password: this.password,
          token: this.token,
        });

        // Redirect to login page after successful reset
        this.$router.push('/login');
      } catch (error) {
        this.errorMessage = error.message || 'Failed to reset password.';
      }
    },
  },
};
</script>

<style scoped>
.reset-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: #f4f7fc;
}

.form-card {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.form-title {
  text-align: center;
  font-size: 24px;
  margin-bottom: 20px;
  color: #333;
}

.reset-password-form {
  display: flex;
  flex-direction: column;
}

.input-group {
  margin-bottom: 20px;
}

.input-label {
  font-size: 14px;
  color: #555;
  margin-bottom: 8px;
}

.input-field {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 16px;
  color: #333;
  box-sizing: border-box;
}

.input-field:focus {
  border-color: #5e72e4;
  outline: none;
  box-shadow: 0 0 5px rgba(94, 114, 228, 0.2);
}

.submit-btn {
  padding: 14px;
  background-color: #5e72e4;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.submit-btn:hover {
  background-color: #4b60c4;
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
  text-align: center;
}
</style>
