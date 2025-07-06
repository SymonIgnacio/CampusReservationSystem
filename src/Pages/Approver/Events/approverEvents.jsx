import React, { useState, useEffect } from 'react';
import './approverEvents.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ApproverEvents = () => {
  const [events, setEvents] = useState([]);
  const [facilities, setFacilities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [formData, setFormData] = useState({
    activity: '',
    purpose: '',
    date_need_from: '',
    date_need_until: '',
    start_time: '',
    end_time: '',
    venue: '',
    request_by: '',
    department_organization: '',
    participants: '',
    total_male_attendees: 0,
    total_female_attendees: 0,
    equipments_needed: ''
  });

  useEffect(() => {
    fetchEvents();
    fetchFacilities();
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/get_approved_requests.php`);
      const data = await response.json();
      if (data.success) {
        setEvents(data.events || []);
      }
      setLoading(false);
    } catch (error) {
      console.error('Error fetching events:', error);
      setLoading(false);
    }
  };

  const fetchFacilities = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/get_facilities.php`);
      const data = await response.json();
      if (data.success) {
        setFacilities(data.facilities || []);
      }
    } catch (error) {
      console.error('Error fetching facilities:', error);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${API_BASE_URL}/create_event.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
        mode: 'no-cors'
      });

      // Reset form and close modal
      setFormData({
        activity: '',
        purpose: '',
        date_need_from: '',
        date_need_until: '',
        start_time: '',
        end_time: '',
        venue: '',
        request_by: '',
        department_organization: '',
        participants: '',
        total_male_attendees: 0,
        total_female_attendees: 0,
        equipments_needed: ''
      });
      setShowCreateModal(false);
      fetchEvents(); // Refresh events list
      alert('Event created successfully!');
    } catch (error) {
      console.error('Error creating event:', error);
      alert('Error creating event. Please try again.');
    }
  };

  return (
    <div className="approver-events">
      <div className="events-header">
        <h1 className="page-title">EVENTS</h1>
        <button 
          className="create-event-btn"
          onClick={() => setShowCreateModal(true)}
        >
          Create Event
        </button>
      </div>

      <div className="events-list">
        {loading ? (
          <p>Loading events...</p>
        ) : events.length > 0 ? (
          <div className="events-grid">
            {events.map(event => (
              <div key={event.id} className="event-card">
                <h3>{event.activity}</h3>
                <p><strong>Date:</strong> {new Date(event.date_need_from).toLocaleDateString()}</p>
                <p><strong>Time:</strong> {event.start_time} - {event.end_time}</p>
                <p><strong>Venue:</strong> {event.venue}</p>
                <p><strong>Organizer:</strong> {event.request_by}</p>
                <p><strong>Department:</strong> {event.department_organization}</p>
              </div>
            ))}
          </div>
        ) : (
          <p>No events found.</p>
        )}
      </div>

      {/* Create Event Modal */}
      {showCreateModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Create New Event</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Event Name</label>
                <input
                  type="text"
                  name="activity"
                  value={formData.activity}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="form-group">
                <label>Purpose</label>
                <textarea
                  name="purpose"
                  value={formData.purpose}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Start Date</label>
                  <input
                    type="date"
                    name="date_need_from"
                    value={formData.date_need_from}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label>End Date</label>
                  <input
                    type="date"
                    name="date_need_until"
                    value={formData.date_need_until}
                    onChange={handleInputChange}
                    required
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Start Time</label>
                  <input
                    type="time"
                    name="start_time"
                    value={formData.start_time}
                    onChange={handleInputChange}
                    required
                  />
                </div>
                <div className="form-group">
                  <label>End Time</label>
                  <input
                    type="time"
                    name="end_time"
                    value={formData.end_time}
                    onChange={handleInputChange}
                    required
                  />
                </div>
              </div>

              <div className="form-group">
                <label>Venue</label>
                <select
                  name="venue"
                  value={formData.venue}
                  onChange={handleInputChange}
                  required
                >
                  <option value="">Select Venue</option>
                  {facilities.map(facility => (
                    <option key={facility.id} value={facility.venue || facility.name}>
                      {facility.venue || facility.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Organizer</label>
                <input
                  type="text"
                  name="request_by"
                  value={formData.request_by}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="form-group">
                <label>Department</label>
                <input
                  type="text"
                  name="department_organization"
                  value={formData.department_organization}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Create Event</button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={() => setShowCreateModal(false)}
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ApproverEvents;