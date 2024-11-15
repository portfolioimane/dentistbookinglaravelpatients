<template>
  <div class="my-appointments">
    <h1>My Appointments</h1>
    
    <!-- Loading state -->
    <div v-if="loading" class="loading">
      <p>Loading...</p>
    </div>

    <!-- Display appointments in a table -->
    <div v-else-if="appointments.length > 0">
      <table class="appointments-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="appointment in appointments" :key="appointment.id">
            <td>{{ appointment.service.name }}</td>
            <td>{{ appointment.date }}</td>
            <td>{{ appointment.start_time }}</td>
            <td>{{ appointment.end_time }}</td>
            <td>{{ appointment.status }}</td>
            <td>
              <button 
                v-if="appointment.status === 'pending'" 
                @click="confirm(appointment.id)"
              >
                Confirm
              </button>
              <button 
                v-if="appointment.status === 'pending'" 
                class="cancel-button"
                @click="cancelAppointment(appointment.id)"
              >
                Cancel 
              </button>
              <!-- Show 'No actions' if no buttons available -->
              <span v-else>No actions available</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- No appointments message -->
    <div v-else>
      <p>You have no upcoming appointments.</p>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Swal from 'sweetalert2'; // Import SweetAlert2

export default {
  name: 'MyAppointments',
  data() {
    return {
      loading: true, // Initial loading state
    };
  },
  computed: {
    ...mapState('appointments', ['appointments']), // Map the appointments from Vuex state
  },
  methods: {
    ...mapActions('appointments', ['fetchMyAppointments', 'cancelBooking']), // Map actions

    // Confirm appointment using SweetAlert
    async confirm(appointmentId) {
      try {
        this.loading = true; // Show loading while processing

        // Navigate to the ConfirmBooking page with the appointment id
        this.$router.push({ name: 'ConfirmBooking', params: { id: appointmentId } });

        this.loading = false; // Stop loading after redirect
      } catch (error) {
        this.loading = false; // Stop loading if there’s an error
        console.error('Error confirming:', error);
      }
    },

    // Cancel appointment using SweetAlert for confirmation
    async cancelAppointment(appointmentId) {
      try {
        // SweetAlert confirmation dialog
        const result = await Swal.fire({
          title: 'Are you sure?',
          text: 'Do you really want to cancel this appointment?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, cancel it!',
          cancelButtonText: 'No, keep it',
        });

        if (result.isConfirmed) {
          this.loading = true; // Show loading while canceling
          await this.cancelBooking({ id: appointmentId }); // Call the cancel action
          await this.fetchMyAppointments(); // Refresh appointments list
          this.loading = false; // Stop loading after completion
          Swal.fire('Cancelled!', 'Your appointment has been cancelled.', 'success');
        }
      } catch (error) {
        this.loading = false; // Stop loading if there’s an error
        console.error('Error cancelling appointment:', error);
        Swal.fire('Error!', 'Appointment cancellation failed. Please try again.', 'error');
      }
    },
  },
  async created() {
    try {
      await this.fetchMyAppointments();
    } catch (error) {
      console.error('Error fetching appointments:', error);
    } finally {
      this.loading = false; // Ensure loading is set to false after data is fetched
    }
  },
};
</script>

<style scoped>
.my-appointments {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.appointments-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.appointments-table th,
.appointments-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.appointments-table th {
  background-color: #f4f4f4;
  font-weight: bold;
}

button {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 5px;
}

button:hover {
  background-color: #45a049;
}

.cancel-button {
  background-color: #f44336;
}

.cancel-button:hover {
  background-color: #e53935;
}

.loading {
  text-align: center;
  font-size: 1.2em;
  margin: 20px 0;
}

span {
  color: #aaa;
  font-style: italic;
}
</style>
