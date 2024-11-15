<template>
  <div class="appointments-container">
    <h2>Appointments</h2>
    <!-- Wrapping the table in a scrollable container -->
    <div class="table-wrapper">
      <table class="appointments-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Date</th>
            <th>Time</th> <!-- Merged Start and End Time into a single column -->
            <th>Service</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="appointment in appointments" :key="appointment.id">
            <td>{{ appointment.name }}</td>
            <td>{{ appointment.phone }}</td>
            <td>{{ appointment.date }}</td>
            <td>{{ appointment.start_time }} - {{ appointment.end_time }}</td> <!-- Combined Start and End Time -->
            <td>{{ appointment.service.name }}</td>
            <td>
              <select v-model="appointment.status" @change="updateStatus(appointment.id, appointment.status)" class="status-select">
                <option v-for="status in possibleStatuses" :key="status" :value="status">{{ status }}</option>
              </select>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="deleteAppointment(appointment.id)" class="delete-button">Delete</button>
                <button @click="viewDetails(appointment.id)" class="details-button">View Details</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      possibleStatuses: ['pending', 'confirmed', 'completed', 'canceled'],
    };
  },
  computed: {
    appointments() {
      return this.$store.getters.allAppointments;
    },
  },
  created() {
    this.$store.dispatch('fetchAppointments');
  },
  methods: {
    updateStatus(appointmentId, newStatus) {
      this.$store.dispatch('changeAppointmentStatus', { appointmentId, newStatus });
    },
    deleteAppointment(appointmentId) {
      this.$store.dispatch('deleteAppointment', appointmentId);
    },
    viewDetails(appointmentId) {
      console.log(`Viewing details for appointment ID: ${appointmentId}`);
    },
  },
};
</script>

<style scoped>
.appointments-container {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

/* Wrapping table in a scrollable container */
.table-wrapper {
  overflow-x: auto;
  margin-top: 20px;
}

/* Table styling */
.appointments-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: auto; /* Allow columns to adjust to content */
}

/* Header and Cell Styling */
.appointments-table th,
.appointments-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.appointments-table th {
  background-color: #2196F3;
  color: white;
  text-transform: uppercase;
}

.appointments-table tbody tr:hover {
  background-color: #f5f5f5;
}

/* Status dropdown styles */
.status-select {
  padding: 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
  font-size: 14px;
  cursor: pointer;
  background-color: #f9f9f9;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.status-select:focus {
  border-color: #2196F3;
  box-shadow: 0 0 5px rgba(33, 150, 243, 0.5);
}

/* Action buttons styling */
.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.delete-button {
  background-color: #f44336;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #d32f2f;
}

.details-button {
  background-color: #2196F3;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
}

.details-button:hover {
  background-color: #1976D2;
}
</style>
