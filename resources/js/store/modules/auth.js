import axios from '../../utils/axios.js';

const state = {
  user: null,
  token: localStorage.getItem('user-token') || '',
  authChecked: false, // New loading flag for authentication check
};

const mutations = {
  SET_USER(state, user) {
    state.user = user;
  },
  SET_TOKEN(state, token) {
    state.token = token;
  },
  SET_AUTH_CHECKED(state, value) {
    state.authChecked = value;
  },
};


const actions = {
  async login({ commit }, credentials) {
    try {
      const response = await axios.post('/api/login', credentials);
      const token = response.data.token;
      const user = response.data.user;

      localStorage.setItem('user-token', token);
     localStorage.setItem('user-role', user.role);

      commit('SET_TOKEN', token);
      commit('SET_USER', user);
      console.log('Login successful:', response.data);
    } catch (error) {
      console.error('Login failed:', error.response.data);
      throw error.response.data;
    }
  },

  async register({ dispatch }, userData) {
    try {
      const response = await axios.post('/api/register', userData);
      console.log('Registration successful:', response.data);

      // Automatically log the user in after registration
      const loginData = {
        email: userData.email,
        password: userData.password,
      };
      await dispatch('login', loginData); // Call the login action with the new user's credentials
    } catch (error) {
      console.error('Registration failed:', error.response.data);
      throw error.response.data;
    }
  },

  async logout({ commit }) {
    console.log('Logging out...'); // Debug log
    localStorage.removeItem('user-token');
    localStorage.removeItem('user-role');

    commit('SET_TOKEN', '');
    commit('SET_USER', null);
    console.log('Logout successful'); // Debug log
  },

async checkAuth({ commit }) {
  const token = localStorage.getItem('user-token');
  commit('SET_AUTH_CHECKED', false); // Set loading to true at the beginning
  if (token) {
    commit('SET_TOKEN', token);
    try {
      const response = await axios.get('/api/user');
      commit('SET_USER', response.data.user);
    } catch (error) {
      console.error('Failed to fetch user data:', error);
      commit('SET_USER', null);
    }
  } else {
    commit('SET_USER', null);
    commit('SET_TOKEN', '');
  }
  commit('SET_AUTH_CHECKED', true); // Set loading to false once check is complete
},

async resetPassword({ commit }, { email, password, token }) {
  try {
    // Send a POST request to reset the password
    const response = await axios.post('/api/password/reset', {
      email,
      password,
      password_confirmation: password, // Ensure the password and confirmation are the same
      token,
    });

    // Handle the successful response
    console.log('Password reset successful:', response.data);

    // Set 'resetpassword' to true in localStorage
    localStorage.setItem('resetpassword', 'true'); // Save the reset password flag in localStorage

    return response.data; // Optionally return the success message
  } catch (error) {
    console.error('Password reset failed:', error.response.data);
    throw error.response.data; // Handle error and pass it to the component
  }
},
async sendPasswordResetLink({ commit }, { email }) {
      try {
        const response = await axios.post('/api/password/email', { email });
        return response.data;  // Handle success response
      } catch (err) {
        throw err;  // Handle error response
      }
    },

};

const getters = {
  isAuthenticated: (state) => !!state.token, // Check token from Vuex state
  user: (state) => state.user,
 authChecked: (state) => state.authChecked,

};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
};
