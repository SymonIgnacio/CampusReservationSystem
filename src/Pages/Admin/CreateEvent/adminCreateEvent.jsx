import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../../context/AuthContext';
import './adminCreateEvent.css';

// Department options
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

const AdminCreateEvent = () => {
  const { user } = useContext(AuthContext);
  const [formData, setFormData] = useState(() => {
    const saved = localStorage.getItem('adminCreateEventForm');
    return saved ? JSON.parse(saved) : {
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
    };
  });

  const [venues, setVenues] = useState([]);
  const [equipment, setEquipment] = useState([]);
  const [equipmentStock, setEquipmentStock] = useState({});
  const [referenceNumber, setReferenceNumber] = useState('');
  const [currentDate, setCurrentDate] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitMessage, setSubmitMessage] = useState({ type: '', text: '' });
  const [selectedEquipment, setSelectedEquipment] = useState('');
  const [equipmentQuantity, setEquipmentQuantity] = useState(0);

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
  }, []);
  
  // Fetch venues and equipment from the database
  useEffect(() => {
    const fetchVenues = async () => {
      try {
        const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_facilities.php', {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        
        if (data.success && data.facilities) {
          // Map the facilities to match the venue format needed for the dropdown
          const venueOptions = data.facilities.map(facility => ({
            id: facility.resource_id,
            name: facility.name // This is the venue name from the database
          }));
          setVenues(venueOptions);
        } else {
          console.error('Failed to fetch venues:', data.message);
        }
      } catch (error) {
        console.error('Error fetching venues:', error);
      }
    };

    const fetchEquipment = async () => {
      try {
        const checkDate = formData.dateFrom || new Date().toISOString().split('T')[0];
        const response = await fetch(`http://localhost/CampusReservationSystem/src/api/equipment_availability.php?date=${checkDate}`, {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        
        if (data.success && data.equipment) {
          // Map the equipment data for the dropdown
          const equipmentOptions = data.equipment.map(item => ({
            id: item.equipment_id,
            name: item.name
          }));
          setEquipment(equipmentOptions);
          
          // Create a map of equipment ID to available quantity
          const stockMap = {};
          data.equipment.forEach(item => {
            stockMap[item.equipment_id] = item.available_quantity;
          });
          setEquipmentStock(stockMap);
        } else {
          console.error('Failed to fetch equipment:', data.message);
        }
      } catch (error) {
        console.error('Error fetching equipment:', error);
      }
    };

    fetchVenues();
    fetchEquipment();
  }, [formData.dateFrom]);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    
    let newFormData;
    if (name === 'activityNature' && value !== 'others') {
      newFormData = {
        ...formData,
        [name]: value,
        otherNature: '' // Clear other nature if not selecting "others"
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
    localStorage.setItem('adminCreateEventForm', JSON.stringify(newFormData));
  };

  const addEquipment = () => {
    if (!selectedEquipment || equipmentQuantity < 1) return;
    
    // Find the equipment object
    const equipmentObj = equipment.find(e => e.id.toString() === selectedEquipment);
    if (!equipmentObj) return;
    
    // Get available stock
    const availableStock = equipmentStock[selectedEquipment] || 0;
    
    // Validate quantity against stock
    if (equipmentQuantity > availableStock) {
      setSubmitMessage({
        type: 'error',
        text: `Only ${availableStock} ${equipmentObj.name}(s) available in stock.`
      });
      return;
    }
    
    // Check if already in the list
    if (formData.equipmentNeeded.includes(parseInt(selectedEquipment))) {
      // Update quantity only
      const newFormData = {
        ...formData,
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      };
      setFormData(newFormData);
      localStorage.setItem('adminCreateEventForm', JSON.stringify(newFormData));
    } else {
      // Add new equipment
      const newFormData = {
        ...formData,
        equipmentNeeded: [...formData.equipmentNeeded, parseInt(selectedEquipment)],
        equipmentQuantities: {
          ...formData.equipmentQuantities,
          [selectedEquipment]: equipmentQuantity
        }
      };
      setFormData(newFormData);
      localStorage.setItem('adminCreateEventForm', JSON.stringify(newFormData));
    }
    
    // Reset selection
    setSelectedEquipment('');
    setEquipmentQuantity(0);
    setSubmitMessage({ type: '', text: '' });
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
    localStorage.setItem('adminCreateEventForm', JSON.stringify(newFormData));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitMessage({ type: '', text: '' });
    
    try {
      // First check if the venue is available
      const availabilityCheck = await fetch('http://localhost/CampusReservationSystem/src/api/check_venue_availability.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        mode: 'cors',
        body: JSON.stringify({
          venue: formData.venue,
          dateFrom: formData.dateFrom,
          dateTo: formData.dateTo,
          timeStart: formData.timeStart,
          timeEnd: formData.timeEnd
        }),
      });
      
      const availabilityResult = await availabilityCheck.json();
      
      if (!availabilityResult.available) {
        // Venue is not available, show conflicts
        const conflicts = availabilityResult.conflicts;
        let conflictMessage = 'The venue is already booked for the following dates/times:\n\n';
        
        conflicts.forEach(conflict => {
          conflictMessage += `- ${conflict.date} at ${conflict.time} by ${conflict.department} (${conflict.activity})\n`;
        });
        
        setSubmitMessage({ 
          type: 'error', 
          text: conflictMessage
        });
        setIsSubmitting(false);
        return;
      }
      
      // Venue is available, proceed with event creation
      const eventData = {
        eventName: formData.eventName,
        purpose: formData.purpose,
        dateFrom: formData.dateFrom,
        dateTo: formData.dateTo,
        timeStart: formData.timeStart,
        timeEnd: formData.timeEnd,
        venue: formData.venue,
        organizer: formData.organizer,
        department: formData.department,
        participants: formData.participants,
        malePax: formData.malePax,
        femalePax: formData.femalePax,
        totalPax: formData.totalPax,
        activityNature: formData.activityNature,
        otherNature: formData.otherNature,
        equipmentNeeded: formData.equipmentNeeded,
        equipmentQuantities: formData.equipmentQuantities,
        referenceNumber: referenceNumber,
        status: 'approved' // Admin created events are auto-approved
      };
      
      console.log("Sending event data:", eventData);
      
      // Send data to backend using the admin endpoint
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/admin_create_request.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        mode: 'cors',
        body: JSON.stringify(eventData),
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
          text: 'Event created successfully!' 
        });
        
        // Reset form (except reference number and date)
        const resetFormData = {
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
        };
        setFormData(resetFormData);
        localStorage.setItem('adminCreateEventForm', JSON.stringify(resetFormData));
        
        // Generate new reference number for next event
        const digits = Math.floor(100000 + Math.random() * 900000);
        setReferenceNumber(`REQ-${digits}`);
      } else {
        setSubmitMessage({ 
          type: 'error', 
          text: result.message || 'Failed to create event. Please try again.' 
        });
      }
    } catch (error) {
      console.error('Error creating event:', error);
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
      <h2>CREATE EVENT</h2>

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
              placeholder="Select Department"
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
            {isSubmitting ? 'CREATING...' : 'CREATE EVENT'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default AdminCreateEvent;