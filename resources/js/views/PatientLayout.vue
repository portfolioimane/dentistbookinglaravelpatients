<template>
  <div class="patient-dashboard">
    <aside class="sidebar">
      <h2>Welcome!</h2>
      <ul>
        <li>
          <router-link 
            to="/patientdashboard/myappointments" 
            class="sidebar-link" 
            :class="{ active: isActive('/patientdashboard/myappointments') }">
            <i class="material-icons sidebar-icon">event</i>
            My Appointments
          </router-link>
        </li>
        <li>
          <router-link 
            to="/patientdashboard/profile" 
            class="sidebar-link" 
            :class="{ active: isActive('/patientdashboard/profile') }">
            <i class="material-icons sidebar-icon">person</i>
            My Profile
          </router-link>
        </li>
      </ul>
    </aside>
    <main class="content">
      <router-view /> <!-- Nested views rendered here -->
    </main>
  </div>
</template>

<script>
export default {
  name: 'PatientLayout',
  methods: {
    isActive(route) {
      return this.$route.path === route;
    }
  }
};
</script>

<style scoped>
.patient-dashboard {
  display: flex;
  min-height: 100vh;
  background-color: #f9f9f9; /* Soft background for the page */
}

.sidebar {
  width: 240px;
  background-color: #f1f7ff; /* Light blue for a calming effect */
  color: #333; /* Darker text for readability */
  display: flex;
  flex-direction: column;
  padding: 20px;
  border-right: 1px solid #ddd; /* Subtle border for separation */
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); /* Gentle shadow */
}

.sidebar h2 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 30px;
  color: #3f51b5; /* Slightly bold blue for text emphasis */
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar li {
  margin-bottom: 15px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  text-decoration: none;
  color: #333;
  font-size: 16px;
  font-weight: 500;
  background-color: #fff; /* White background for link cards */
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Slight shadow for card effect */
}

.sidebar-link i {
  margin-right: 10px;
  color: #3f51b5; /* Blue icon for consistency */
}

.sidebar-link:hover {
  background-color: #e8f0fe; /* Subtle hover effect */
}

.sidebar-link.active {
  background-color: #d0e3ff; /* Highlight for the active link */
  font-weight: bold;
  color: #3f51b5;
  box-shadow: 0 4px 10px rgba(63, 81, 181, 0.2); /* Stronger shadow */
}

.content {
  flex-grow: 1;
  padding: 20px;
  background-color: #ffffff; /* Clean white for content */
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1); /* Subtle inset shadow */
  overflow-y: auto;
}
</style>
