import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import 'boxicons/css/boxicons.min.css';
import './requestEvent.css';

// Base API URL
const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const RequestEvent = () => {
  const { user } = useContext(AuthContext);
  const [activeTab, setActiveTab] = useState('pending');
  const [showRequestModal, setShowRequestModal] = useState(false);
  const [showDetailsModal, setShowDetailsModal] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState(null);
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
      const response = await fetch(`${API_BASE_URL}/get_user_requests.php?status=${status}&firebase_uid=${user.firebase_uid}`);
      const data = await response.json();
      
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
                  <tr 
                    key={index} 
                    onClick={() => {
                      if (activeTab === 'declined') {
                        setSelectedRequest(request);
                        setShowDetailsModal(true);
                      }
                    }}
                    style={activeTab === 'declined' ? {cursor: 'pointer'} : {}}
                  >
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

      {showDetailsModal && selectedRequest && (
        <div className="modal-overlay">
          <div className="modal-content">
            <button 
              className="close-modal-btn"
              onClick={() => setShowDetailsModal(false)}
            >
              &times;
            </button>
            <div className="request-details">
              <h2>Request Details</h2>
              <div className="details-grid">
                <div><strong>Reference Number:</strong> {selectedRequest.reference_number}</div>
                <div><strong>Activity:</strong> {selectedRequest.activity}</div>
                <div><strong>Department:</strong> {selectedRequest.department_organization}</div>
                <div><strong>Purpose:</strong> {selectedRequest.purpose}</div>
                <div><strong>Date From:</strong> {selectedRequest.date_need_from}</div>
                <div><strong>Date Until:</strong> {selectedRequest.date_need_until}</div>
                <div><strong>Time:</strong> {selectedRequest.start_time} - {selectedRequest.end_time}</div>
                <div><strong>Venue:</strong> {selectedRequest.venue}</div>
                <div><strong>Participants:</strong> {selectedRequest.participants}</div>
                <div><strong>Male Attendees:</strong> {selectedRequest.total_male_attendees}</div>
                <div><strong>Female Attendees:</strong> {selectedRequest.total_female_attendees}</div>
                <div><strong>Total Attendees:</strong> {selectedRequest.total_attendees}</div>
                <div><strong>Equipment:</strong> {selectedRequest.equipments_needed || 'None'}</div>
                {selectedRequest.reason && (
                  <div className="decline-reason">
                    <strong>Reason for Decline:</strong>
                    <p>{selectedRequest.reason}</p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

const RequestVenueForm = ({ onRequestSubmitted }) => {
  const { user } = useContext(AuthContext);
  const [formData, setFormData] = useState(() => ({
    eventName: '',
    organizer: '',
    department: 'Select Department',
    purpose: '',
    activityNature: 'curricular',
    otherNature: '',
    dateFrom: '',
    dateTo: '',
    timeStart: '',
    timeEnd: '',
    participants: '',
    malePax: 0,
    femalePax: 0,
    totalPax: 0,
    venue: '',
    equipmentNeeded: [],
    equipmentQuantities: {}
  }));

  const [venues, setVenues] = useState([]);
  const [equipment, setEquipment] = useState([]);
  const [equipmentStock, setEquipmentStock] = useState({});
  const [referenceNumber, setReferenceNumber] = useState('');
  const [currentDate, setCurrentDate] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [selectedEquipment, setSelectedEquipment] = useState('');
  const [equipmentQuantity, setEquipmentQuantity] = useState(0);

  const departments = [
    'Select Department',
    'College of Computer Studies',
    'College of Accountancy',
    'College of Arts And Science',
    'College Of Education',
    'College of Hospitality Management and Tourism',
    'College Of Business Administration',
    'College of Health and Sciences',
    'School of Psychology',
    'College of Maritime Education',
    'School of Mechanical Engineering'
  ];

  useEffect(() => {
    const digits = Math.floor(100000 + Math.random() * 900000);
    setReferenceNumber(`REQ-${digits}`);

    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    setCurrentDate(`${year}-${month}-${day}`);
    
    // Auto-fill organizer name from user context
    if (user) {
      setFormData(prev => ({
        ...prev,
        organizer: `${user.firstname || ''} ${user.lastname || ''}`.trim() || user.username || ''
      }));
    }
  }, [user]);
  
  useEffect(() => {
    const fetchVenues = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/get_venues.php`);
        const data = await response.json();
        
        if (data.status === 'success') {
          setVenues(data.venues || []);
        }
      } catch (err) {
        console.error('Error fetching venues:', err);
      }
    };

    const fetchEquipment = async () => {
      try {
        const checkDate = formData.dateFrom || new Date().toISOString().split('T')[0];
        const response = await fetch(`${API_BASE_URL}/equipment_availability.php?date=${checkDate}`);
        const data = await response.json();
        
        if (data.success && data.equipment) {
          const equipmentOptions = data.equipment.map(item => ({
            id: item.equipment_id,
            name: item.name
          }));
          setEquipment(equipmentOptions);
          
          const stockMap = {};
          data.equipment.forEach(item => {
            stockMap[item.equipment_id] = item.available_quantity;
          });
          setEquipmentStock(stockMap);
        }
      } catch (error) {
        console.error('Error fetching equipment:', error);
      }
    };

    fetchVenues();
    fetchEquipment();
  }, [formData.dateFrom]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    
    let newFormData;
    if (name === 'activityNature' && value !== 'others') {
      newFormData = {
        ...formData,
        [name]: value,
        otherNature: ''
      };
    } else if (name === 'malePax' || name === 'femalePax') {
      const newValue = parseInt(value) || 0;
      const otherField = name === 'malePax' ? 'femalePax' : 'malePax';
      const otherValue = parseInt(formData[otherField]) || 0;
      
      newFormData = {
        ...formData,
        [name]: newValue,
        totalPax: newValue + otherValue
      };
    } else {
      newFormData = {
        ...formData,
        [name]: value
      };
    }
    
    setFormData(newFormData);
  };

  const addEquipment = () => {
    if (!selectedEquipment || equipmentQuantity < 1) return;
    
    const equipmentObj = equipment.find(e => e.id.toString() === selectedEquipment);
    if (!equipmentObj) return;
    
    const availableStock = equipmentStock[selectedEquipment] || 0;
    
    if (equipmentQuantity > availableStock) {
      setError(`Only ${availableStock} ${equipmentObj.name}(s) available in stock.`);
      return;
    }
    
    if (formData.equipmentNeeded.includes(parseInt(selectedEquipment))) {
      const newFormData = {
        ...formData,
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      };
      setFormData(newFormData);
    } else {
      const newFormData = {
        ...formData,
        equipmentNeeded: [...formData.equipmentNeeded, parseInt(selectedEquipment)],
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      };
      setFormData(newFormData);
    }
    
    setSelectedEquipment('');
    setEquipmentQuantity(0);
    setError(null);
  };

  const removeEquipment = (id) => {
    const newEquipmentNeeded = formData.equipmentNeeded.filter(eqId => eqId !== id);
    const newQuantities = { ...formData.equipmentQuantities };
    delete newQuantities[id];
    
    const newFormData = {
      ...formData,
      equipmentNeeded: newEquipmentNeeded,
      equipmentQuantities: newQuantities
    };
    
    setFormData(newFormData);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);
    
    try {
      const eventData = {
        activity: formData.eventName,
        purpose: formData.purpose,
        date_need_from: `${formData.dateFrom} ${formData.timeStart}`,
        date_need_until: `${formData.dateTo} ${formData.timeEnd}`,
        venue: formData.venue,
        department: formData.department,
        participants: formData.participants,
        malePax: formData.malePax,
        femalePax: formData.femalePax,
        activityNature: formData.activityNature,
        firebase_uid: user.firebase_uid,
        equipment: formData.equipmentNeeded.length > 0 ? formData.equipmentNeeded.map(eqId => {
          const eq = equipment.find(e => e.id === eqId);
          return eq ? `${eq.name} (${formData.equipmentQuantities[eqId]})` : '';
        }).filter(item => item).join(', ') : ''
      };
      
      console.log('Sending data:', eventData);
      
      const response = await fetch(`${API_BASE_URL}/create_request.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(eventData),
      });
      
      const responseText = await response.text();
      console.log('Raw response:', responseText);
      
      if (responseText.includes('<')) {
        console.error('HTML Response:', responseText);
        throw new Error('Server returned HTML error instead of JSON');
      }
      
      const result = JSON.parse(responseText);
      
      if (result.status === 'success') {
        setSuccess('Request submitted successfully!');
        // Reset form
        setFormData({
          eventName: '',
          organizer: '',
          department: 'Select Department',
          purpose: '',
          activityNature: 'curricular',
          otherNature: '',
          dateFrom: '',
          dateTo: '',
          timeStart: '',
          timeEnd: '',
          participants: '',
          malePax: 0,
          femalePax: 0,
          totalPax: 0,
          venue: '',
          equipmentNeeded: [],
          equipmentQuantities: {}
        });
        setTimeout(() => {
          onRequestSubmitted();
        }, 1500);
      } else {
        throw new Error(result.message || 'Failed to submit request');
      }
    } catch (err) {
      console.error('Error submitting request:', err);
      setError('Failed to submit request. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="form-container">
      <h2>REQUEST EVENT</h2>

      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}

      <form onSubmit={handleSubmit}>
        <div className="top-fields">
          <div>
            <label>REFERENCE NO.</label>
            <input 
              type="text" 
              value={referenceNumber} 
              readOnly 
              className="reference-number"
            />
          </div>
          <div>
            <label>DATE OF REQUEST:</label>
            <input 
              type="date" 
              value={currentDate} 
              readOnly 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>NAME OF EVENT:</label>
            <input 
              type="text" 
              name="eventName" 
              value={formData.eventName} 
              onChange={handleChange} 
              required 
            />
          </div>
          
          <div className="form-group">
            <label>DEPARTMENT / ORGANIZATION:</label>
            <select 
              name="department" 
              value={formData.department} 
              onChange={handleChange} 
              required
            >
              {departments.map(dept => (
                <option key={dept} value={dept}>{dept}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>NAME OF ORGANIZER:</label>
            <input 
              type="text" 
              name="organizer" 
              value={formData.organizer} 
              onChange={handleChange} 
              required 
            />
          </div>
          
          <div className="form-group">
            <label>PURPOSE:</label>
            <input 
              type="text" 
              name="purpose" 
              value={formData.purpose} 
              onChange={handleChange} 
              required 
            />
          </div>
        </div>

        <div className="form-section">
          <label>NATURE OF ACTIVITY:</label>
          <div className="radio-group">
            <label>
              <input 
                type="radio" 
                name="activityNature" 
                value="curricular" 
                checked={formData.activityNature === 'curricular'} 
                onChange={handleChange} 
              /> 
              CURRICULAR
            </label>
            <label>
              <input 
                type="radio" 
                name="activityNature" 
                value="co-curricular" 
                checked={formData.activityNature === 'co-curricular'} 
                onChange={handleChange} 
              /> 
              CO-CURRICULAR
            </label>
            <label>
              <input 
                type="radio" 
                name="activityNature" 
                value="others" 
                checked={formData.activityNature === 'others'} 
                onChange={handleChange} 
              /> 
              OTHERS
            </label>
            {formData.activityNature === 'others' && (
              <input 
                type="text" 
                name="otherNature" 
                value={formData.otherNature} 
                onChange={handleChange} 
                placeholder="(PLEASE SPECIFY)" 
                required={formData.activityNature === 'others'}
              />
            )}
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>DATE/S NEEDED:</label>
            <div className="range-group">
              <label>FROM: 
                <input 
                  type="date" 
                  name="dateFrom" 
                  value={formData.dateFrom} 
                  onChange={handleChange} 
                  required 
                />
              </label>
              <label>TO: 
                <input 
                  type="date" 
                  name="dateTo" 
                  value={formData.dateTo} 
                  onChange={handleChange} 
                  required 
                />
              </label>
            </div>
          </div>

          <div className="form-group">
            <label>TIME NEEDED:</label>
            <div className="range-group">
              <label>START: 
                <input 
                  type="time" 
                  name="timeStart" 
                  value={formData.timeStart} 
                  onChange={handleChange} 
                  required 
                />
              </label>
              <label>END: 
                <input 
                  type="time" 
                  name="timeEnd" 
                  value={formData.timeEnd} 
                  onChange={handleChange} 
                  required 
                />
              </label>
            </div>
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>PARTICIPANTS:</label>
            <input 
              type="text" 
              name="participants" 
              value={formData.participants} 
              onChange={handleChange} 
              placeholder="Description of participants" 
              required 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group pax-group">
            <label>NO. OF PAX:</label>
            <div className="pax-inputs">
              <div>
                <label>MALE: 
                  <input 
                    type="number" 
                    name="malePax" 
                    value={formData.malePax} 
                    onChange={handleChange} 
                    min="0" 
                  />
                </label>
              </div>
              <div>
                <label>FEMALE: 
                  <input 
                    type="number" 
                    name="femalePax" 
                    value={formData.femalePax} 
                    onChange={handleChange} 
                    min="0" 
                  />
                </label>
              </div>
              <div>
                <label>TOTAL: 
                  <input 
                    type="number" 
                    value={formData.totalPax} 
                    readOnly 
                  />
                </label>
              </div>
            </div>
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>VENUE:</label>
            <select 
              name="venue" 
              value={formData.venue} 
              onChange={handleChange} 
              required
            >
              <option value="">SELECT</option>
              {venues.map(venue => (
                <option key={venue.id} value={venue.venue}>
                  {venue.venue}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className="form-section">
          <label>EQUIPMENT / MATERIALS NEEDED:</label>
          <div className="equipment-section">
            <div className="equipment-add">
              <select 
                value={selectedEquipment} 
                onChange={(e) => setSelectedEquipment(e.target.value)}
              >
                <option value="">SELECT EQUIPMENT</option>
                {equipment.map(item => (
                  <option key={item.id} value={item.id}>
                    {item.name} (Available: {equipmentStock[item.id] || 0})
                  </option>
                ))}
              </select>
              <div className="quantity-input">
                <label>PCS.: 
                  <input 
                    type="number" 
                    value={equipmentQuantity} 
                    onChange={(e) => setEquipmentQuantity(parseInt(e.target.value) || 0)} 
                    min="0" 
                    max={selectedEquipment ? (equipmentStock[selectedEquipment] || 0) : 1}
                  />
                </label>
              </div>
              <button 
                type="button" 
                onClick={addEquipment} 
                className="add-equipment-btn"
              >
                Add
              </button>
            </div>
            
            {formData.equipmentNeeded.length > 0 && (
              <div className="equipment-list">
                <h4>Selected Equipment:</h4>
                <ul>
                  {formData.equipmentNeeded.map(eqId => {
                    const eq = equipment.find(e => e.id === eqId);
                    return (
                      <li key={eqId}>
                        {eq?.name} - {formData.equipmentQuantities[eqId]} pcs
                        <button 
                          type="button" 
                          onClick={() => removeEquipment(eqId)}
                          className="remove-equipment-btn"
                        >
                          Remove
                        </button>
                      </li>
                    );
                  })}
                </ul>
              </div>
            )}
          </div>
        </div>

        <div className="button-container">
          <button 
            type="submit" 
            className="submit-button" 
            disabled={loading}
          >
            {loading ? 'SUBMITTING...' : 'SUBMIT REQUEST'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default RequestEvent;