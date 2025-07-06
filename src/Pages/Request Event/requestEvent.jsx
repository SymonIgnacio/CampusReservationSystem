import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import 'boxicons/css/boxicons.min.css';
import './requestEvent.css';

// Base API URL
const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

// Department options
const departments = [
  'College of Computer Studies',
  'College of Accountancy',
  'College of Education',
  'College of Hotel Management and Tourism'
];

const RequestEvent = () => {
  const { user } = useContext(AuthContext);
  const [activeTab, setActiveTab] = useState('pending');
  const [showRequestModal, setShowRequestModal] = useState(false);
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (user) {
      fetchRequests(activeTab);
    }
  }, [activeTab, user]);

  const fetchRequests = async (status) => {
    if (!user) return;
    
    setLoading(true);
    try {
      // Using simple GET request without custom headers to avoid preflight
      const response = await fetch(`${API_BASE_URL}/get_user_requests.php?status=${status}&user_id=${user.user_id}`);
      
      const text = await response.text();
      let data;
      
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('Invalid JSON response:', text);
        throw new Error('Invalid response from server');
      }
      
      if (data.status === 'success') {
        setRequests(data.requests || []);
        setError(null);
      } else {
        throw new Error(data.message || 'Failed to fetch requests');
      }
    } catch (err) {
      console.error('Error fetching requests:', err);
      setError(`Failed to load requests: ${err.message}`);
      setRequests([]);
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadgeClass = (status) => {
    switch (status.toLowerCase()) {
      case 'pending': return 'status-badge pending';
      case 'approved': return 'status-badge approved';
      case 'declined': return 'status-badge declined';
      default: return 'status-badge';
    }
  };

  return (
    <div className="request-event-container">
      <div className="request-header">
        <h1>My Requests</h1>
        <button 
          className="new-request-btn"
          onClick={() => setShowRequestModal(true)}
        >
          New Request
        </button>
      </div>

      <div className="tabs">
        <button 
          className={`tab-btn ${activeTab === 'pending' ? 'active' : ''}`}
          onClick={() => setActiveTab('pending')}
        >
          Pending
        </button>
        <button 
          className={`tab-btn ${activeTab === 'approved' ? 'active' : ''}`}
          onClick={() => setActiveTab('approved')}
        >
          Approved
        </button>
        <button 
          className={`tab-btn ${activeTab === 'declined' ? 'active' : ''}`}
          onClick={() => setActiveTab('declined')}
        >
          Declined
        </button>
      </div>

      <div className="requests-content">
        {loading ? (
          <div className="loading-message">Loading requests...</div>
        ) : error ? (
          <div className="error-message">{error}</div>
        ) : requests.length > 0 ? (
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>Reference No.</th>
                  <th>Activity</th>
                  <th>Venue</th>
                  <th>Date From</th>
                  <th>Date Until</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {requests.map((request, index) => (
                  <tr key={index}>
                    <td>{request.reference_number}</td>
                    <td>{request.activity}</td>
                    <td>{request.venue}</td>
                    <td>{request.date_need_from}</td>
                    <td>{request.date_need_until}</td>
                    <td>
                      <span className={getStatusBadgeClass(request.status || activeTab)}>
                        {request.status || activeTab}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="no-requests">No {activeTab} requests found.</div>
        )}
      </div>

      {showRequestModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <button 
              className="close-modal-btn"
              onClick={() => setShowRequestModal(false)}
            >
              &times;
            </button>
            <RequestVenueForm 
              onRequestSubmitted={() => {
                setShowRequestModal(false);
                fetchRequests(activeTab);
              }}
            />
          </div>
        </div>
      )}
    </div>
  );
};

const RequestVenueForm = ({ onRequestSubmitted }) => {
  const { user } = useContext(AuthContext);
  const [formData, setFormData] = useState({
    activity: '',
    department: '',
    otherDepartment: '',
    venue: '',
    dateFrom: '',
    timeFrom: '',
    dateTo: '',
    timeTo: '',
    attendees: '',
    purpose: '',
    equipment: []
  });
  const [venues, setVenues] = useState([]);
  const [equipmentList, setEquipmentList] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  // Equipment options
  const availableEquipment = [
    'Projector',
    'Sound System',
    'Microphone',
    'Chairs',
    'Tables',
    'Whiteboard'
  ];

  useEffect(() => {
    // Fetch available venues
    const fetchVenues = async () => {
      try {
        // Simple GET request without custom headers
        const response = await fetch(`${API_BASE_URL}/get_venues.php`);
        
        const text = await response.text();
        let data;
        
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error('Invalid JSON response:', text);
          throw new Error('Invalid response from server');
        }
        
        if (data.status === 'success') {
          setVenues(data.venues || []);
        } else {
          throw new Error(data.message || 'Failed to fetch venues');
        }
      } catch (err) {
        console.error('Error fetching venues:', err);
        setError('Failed to load venues. Please try again later.');
      }
    };

    fetchVenues();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleEquipmentChange = (equipment) => {
    setFormData(prev => {
      const updatedEquipment = prev.equipment.includes(equipment)
        ? prev.equipment.filter(item => item !== equipment)
        : [...prev.equipment, equipment];
      
      return {
        ...prev,
        equipment: updatedEquipment
      };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      // Format dates and times for submission
      const formattedData = {
        ...formData,
        user_id: user.user_id,
        date_need_from: `${formData.dateFrom} ${formData.timeFrom}`,
        date_need_until: `${formData.dateTo} ${formData.timeTo}`,
        equipment: formData.equipment.join(',')
      };

      const response = await fetch(`${API_BASE_URL}/create_request.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formattedData)
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        setSuccess('Request submitted successfully!');
        setTimeout(() => {
          onRequestSubmitted();
        }, 1500);
      } else {
        throw new Error(data.message || 'Failed to submit request');
      }
    } catch (err) {
      console.error('Error submitting request:', err);
      setError(`Failed to submit request: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="request-form-container">
      <h2>Request Event Venue</h2>
      
      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="activity">Activity Name <span className="required">*</span></label>
          <input
            type="text"
            id="activity"
            name="activity"
            value={formData.activity}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="department">Department <span className="required">*</span></label>
          <select
            id="department"
            name="department"
            value={formData.department}
            onChange={handleChange}
            required
          >
            <option value="">Select Department</option>
            {departments.map((dept, index) => (
              <option key={index} value={dept}>{dept}</option>
            ))}
            <option value="others">Others</option>
          </select>
          {formData.department === 'others' && (
            <input
              type="text"
              name="otherDepartment"
              value={formData.otherDepartment || ''}
              onChange={handleChange}
              placeholder="Please specify department"
              required
              style={{marginTop: '10px'}}
            />
          )}
        </div>

        <div className="form-group">
          <label htmlFor="venue">Venue <span className="required">*</span></label>
          <select
            id="venue"
            name="venue"
            value={formData.venue}
            onChange={handleChange}
            required
          >
            <option value="">Select Venue</option>
            {venues.map((venue, index) => (
              <option key={index} value={venue.venue_id || venue.name}>
                {venue.name || venue.venue_name}
              </option>
            ))}
          </select>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="dateFrom">Usage Date From <span className="required">*</span></label>
            <input
              type="date"
              id="dateFrom"
              name="dateFrom"
              value={formData.dateFrom}
              onChange={handleChange}
              required
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="timeFrom">Time From <span className="required">*</span></label>
            <input
              type="time"
              id="timeFrom"
              name="timeFrom"
              value={formData.timeFrom}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="dateTo">Usage Date To <span className="required">*</span></label>
            <input
              type="date"
              id="dateTo"
              name="dateTo"
              value={formData.dateTo}
              onChange={handleChange}
              required
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="timeTo">Time To <span className="required">*</span></label>
            <input
              type="time"
              id="timeTo"
              name="timeTo"
              value={formData.timeTo}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        <div className="form-group">
          <label htmlFor="attendees">Number of Attendees <span className="required">*</span></label>
          <input
            type="number"
            id="attendees"
            name="attendees"
            value={formData.attendees}
            onChange={handleChange}
            min="1"
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="purpose">Purpose <span className="required">*</span></label>
          <textarea
            id="purpose"
            name="purpose"
            value={formData.purpose}
            onChange={handleChange}
            rows="3"
            required
          ></textarea>
        </div>

        <div className="form-group">
          <label>Equipment Needed</label>
          <div className="equipment-options">
            {availableEquipment.map((equipment, index) => (
              <div key={index} className="equipment-option">
                <input
                  type="checkbox"
                  id={`equipment-${index}`}
                  checked={formData.equipment.includes(equipment)}
                  onChange={() => handleEquipmentChange(equipment)}
                />
                <label htmlFor={`equipment-${index}`}>{equipment}</label>
              </div>
            ))}
          </div>
        </div>

        <div className="form-actions">
          <button 
            type="submit" 
            className="submit-btn"
            disabled={loading}
          >
            {loading ? 'Submitting...' : 'Submit Request'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default RequestEvent;
