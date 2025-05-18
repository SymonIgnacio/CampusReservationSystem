import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import './requestEvent.css';

// Sample venues - replace with actual data from your database
const venues = [
  { id: 1, name: "Main Auditorium" },
  { id: 2, name: "Conference Room A" },
  { id: 3, name: "Conference Room B" },
  { id: 4, name: "Sports Hall" },
  { id: 5, name: "Outdoor Field" },
  { id: 6, name: "Classroom 101" },
  { id: 7, name: "Classroom 102" },
  { id: 8, name: "Library Meeting Room" }
];

// Sample equipment - replace with actual data from your database
const equipment = [
  { id: 1, name: "Projector" },
  { id: 2, name: "Microphone" },
  { id: 3, name: "Speakers" },
  { id: 4, name: "Laptop" },
  { id: 5, name: "Whiteboard" },
  { id: 6, name: "Chairs" },
  { id: 7, name: "Tables" },
  { id: 8, name: "Extension Cords" }
];

// Department options
const departments = [
  'College of Computer Studies',
  'College of Accountancy',
  'College of Education',
  'College of Hotel Management and Tourism'
];

const RequestVenueForm = () => {
  const { user } = useContext(AuthContext);
  const [formData, setFormData] = useState({
    requestorName: '',
    department: 'College of Computer Studies',
    activity: '',
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

  const [referenceNumber, setReferenceNumber] = useState('');
  const [currentDate, setCurrentDate] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitMessage, setSubmitMessage] = useState({ type: '', text: '' });
  const [selectedEquipment, setSelectedEquipment] = useState('');
  const [equipmentQuantity, setEquipmentQuantity] = useState(1);

  // Generate reference number and set current date on component mount
  useEffect(() => {
    // Generate reference number: REQ- followed by 6 digits
    const digits = Math.floor(100000 + Math.random() * 900000); // 6 digits
    setReferenceNumber(`REQ-${digits}`);

    // Set current date in YYYY-MM-DD format
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    setCurrentDate(`${year}-${month}-${day}`);

    // Pre-fill requestor name if user is logged in
    if (user) {
      setFormData(prev => ({
        ...prev,
        requestorName: `${user.firstname} ${user.lastname}`,
        department: user.department || 'College of Computer Studies'
      }));
    }
  }, [user]);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    
    if (name === 'activityNature' && value !== 'others') {
      setFormData({
        ...formData,
        [name]: value,
        otherNature: '' // Clear other nature if not selecting "others"
      });
    } else if (name === 'malePax' || name === 'femalePax') {
      const newValue = parseInt(value) || 0;
      const otherField = name === 'malePax' ? 'femalePax' : 'malePax';
      const otherValue = parseInt(formData[otherField]) || 0;
      
      setFormData({
        ...formData,
        [name]: newValue,
        totalPax: newValue + otherValue
      });
    } else {
      setFormData({
        ...formData,
        [name]: value
      });
    }
  };

  const addEquipment = () => {
    if (!selectedEquipment || equipmentQuantity < 1) return;
    
    // Find the equipment object
    const equipmentObj = equipment.find(e => e.id.toString() === selectedEquipment);
    if (!equipmentObj) return;
    
    // Check if already in the list
    if (formData.equipmentNeeded.includes(parseInt(selectedEquipment))) {
      // Update quantity only
      setFormData({
        ...formData,
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      });
    } else {
      // Add new equipment
      setFormData({
        ...formData,
        equipmentNeeded: [...formData.equipmentNeeded, parseInt(selectedEquipment)],
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      });
    }
    
    // Reset selection
    setSelectedEquipment('');
    setEquipmentQuantity(1);
  };

  const removeEquipment = (id) => {
    const newEquipmentNeeded = formData.equipmentNeeded.filter(eqId => eqId !== id);
    const newQuantities = { ...formData.equipmentQuantities };
    delete newQuantities[id];
    
    setFormData({
      ...formData,
      equipmentNeeded: newEquipmentNeeded,
      equipmentQuantities: newQuantities
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitMessage({ type: '', text: '' });
    
    try {
      // Combine form data with reference number and current date
      const requestData = {
        referenceNumber,
        requestDate: currentDate,
        userId: user?.user_id || 1, // Use logged in user ID or default to 1 for testing
        status: 'pending', // Always set to pending for faculty requests
        ...formData
      };
      
      console.log("Sending request data:", requestData);
      
      // Send data to backend
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/create_request.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });
      
      const responseText = await response.text();
      console.log("Raw response:", responseText);
      
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error("Error parsing response:", parseError);
        setSubmitMessage({ 
          type: 'error', 
          text: 'Error parsing server response. Please check the console for details.' 
        });
        setIsSubmitting(false);
        return;
      }
      
      if (result.success) {
        setSubmitMessage({ 
          type: 'success', 
          text: 'Venue request submitted successfully! Your request is pending approval.' 
        });
        
        // Reset form (except reference number and date)
        setFormData({
          requestorName: user ? `${user.firstname} ${user.lastname}` : '',
          department: user?.department || 'College of Computer Studies',
          activity: '',
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
        
        // Generate new reference number for next request
        const digits = Math.floor(100000 + Math.random() * 900000);
        setReferenceNumber(`REQ-${digits}`);
      } else {
        setSubmitMessage({ 
          type: 'error', 
          text: result.message || 'Failed to submit request. Please try again.' 
        });
      }
    } catch (error) {
      console.error('Error submitting request:', error);
      setSubmitMessage({ 
        type: 'error', 
        text: 'Network error. Please check your connection and try again.' 
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="form-container">
      <h2>REQUEST VENUE</h2>

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
            <label>DATE:</label>
            <input 
              type="date" 
              value={currentDate} 
              readOnly 
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>NAME OF REQUESTOR:</label>
            <input 
              type="text" 
              name="requestorName" 
              value={formData.requestorName} 
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
            <label>ACTIVITY:</label>
            <input 
              type="text" 
              name="activity" 
              value={formData.activity} 
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
                <option key={venue.id} value={venue.name}>
                  {venue.name}
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
                    {item.name}
                  </option>
                ))}
              </select>
              <div className="quantity-input">
                <label>PCS.: 
                  <input 
                    type="number" 
                    value={equipmentQuantity} 
                    onChange={(e) => setEquipmentQuantity(parseInt(e.target.value) || 1)} 
                    min="1" 
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

        {submitMessage.text && (
          <div className={`message ${submitMessage.type}`}>
            {submitMessage.text}
          </div>
        )}

        <div className="button-container">
          <button 
            type="submit" 
            className="submit-button" 
            disabled={isSubmitting}
          >
            {isSubmitting ? 'SUBMITTING...' : 'SUBMIT REQUEST'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default RequestVenueForm;