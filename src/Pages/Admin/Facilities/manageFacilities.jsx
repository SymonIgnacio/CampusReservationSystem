import React, { useState, useEffect } from 'react';
import './manageFacilities.css';

function ManageFacilities() {
  const [facilities, setFacilities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [newFacility, setNewFacility] = useState({
    name: '',
    location: '',
    capacity: '',
    description: ''
  });
  const [editFacility, setEditFacility] = useState(null);

  // Fetch facilities on component mount
  useEffect(() => {
    fetchFacilities();
  }, []);

  // Fetch facilities from API
  const fetchFacilities = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_facilities.php');
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        setFacilities(data.facilities || []);
      } else {
        throw new Error(data.message || 'Failed to fetch facilities');
      }
    } catch (error) {
      console.error('Error fetching facilities:', error);
      setError('Failed to load facilities. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Handle input change for new facility form
  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setNewFacility({
      ...newFacility,
      [name]: type === 'checkbox' ? checked : value
    });
  };

  // Handle form submission for adding new facility
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/add_facility.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(newFacility),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Reset form and close modal
        setNewFacility({
          name: '',
          location: '',
          capacity: '',
          description: ''
        });
        setShowAddModal(false);
        
        // Refresh facilities list
        fetchFacilities();
        
        alert('Facility added successfully!');
      } else {
        alert(`Failed to add facility: ${data.message}`);
      }
    } catch (error) {
      console.error('Error adding facility:', error);
      alert(`Error adding facility: ${error.message}`);
    }
  };

  // Open edit modal with facility data
  const handleEdit = (facility) => {
    setEditFacility({
      resource_id: facility.resource_id,
      name: facility.name,
      location: facility.campus || '',
      capacity: facility.capacity || '',
      description: facility.description || ''
    });
    setShowEditModal(true);
  };

  // Handle input change for edit facility form
  const handleEditInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setEditFacility({
      ...editFacility,
      [name]: type === 'checkbox' ? checked : value
    });
  };

  // Handle form submission for updating facility
  const handleUpdateSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/update_facility.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(editFacility),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Close modal
        setShowEditModal(false);
        
        // Refresh facilities list
        fetchFacilities();
        
        alert('Facility updated successfully!');
      } else {
        alert(`Failed to update facility: ${data.message}`);
      }
    } catch (error) {
      console.error('Error updating facility:', error);
      alert(`Error updating facility: ${error.message}`);
    }
  };

  // Handle delete facility
  const handleDelete = async (facilityId) => {
    if (!window.confirm('Are you sure you want to delete this facility?')) {
      return;
    }
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/delete_facility.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ facilityId }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Remove facility from state
        setFacilities(facilities.filter(facility => facility.resource_id !== facilityId));
        alert('Facility deleted successfully!');
      } else {
        alert(`Failed to delete facility: ${data.message}`);
      }
    } catch (error) {
      console.error('Error deleting facility:', error);
      alert(`Error deleting facility: ${error.message}`);
    }
  };

  return (
    <div className="manage-facilities-container">
      <h1 className="page-title">MANAGE FACILITIES</h1>
      
      <div className="controls">
        <button 
          className="add-facility-button"
          onClick={() => setShowAddModal(true)}
        >
          Add New Facility
        </button>
      </div>
      
      {loading ? (
        <div className="loading">Loading facilities...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : facilities.length === 0 ? (
        <div className="no-facilities">No facilities found.</div>
      ) : (
        <div className="facilities-table-container">
          <table className="facilities-table">
            <thead>
              <tr>
                <th>Venue</th>
                <th>Campus</th>
                <th>Capacity</th>
                <th>Description</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {facilities.map(facility => (
                <tr key={facility.resource_id}>
                  <td>{facility.name}</td>
                  <td>{facility.campus || 'N/A'}</td>
                  <td>{facility.capacity || 'N/A'}</td>
                  <td>{facility.description || 'N/A'}</td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="edit-btn"
                        onClick={() => handleEdit(facility)}
                      >
                        Edit
                      </button>
                      <button 
                        className="delete-btn"
                        onClick={() => handleDelete(facility.resource_id)}
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      
      {/* Add Facility Modal */}
      {showAddModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Add New Facility</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Facility Name</label>
                <input 
                  type="text" 
                  name="name" 
                  value={newFacility.name} 
                  onChange={handleInputChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Campus</label>
                <select 
                  name="location" 
                  value={newFacility.location} 
                  onChange={handleInputChange} 
                  required
                >
                  <option value="">Select Campus</option>
                  <option value="Main Campus">Main Campus</option>
                  <option value="East Campus">East Campus</option>
                  <option value="West Campus">West Campus</option>
                  <option value="North Campus">North Campus</option>
                  <option value="South Campus">South Campus</option>
                </select>
              </div>
              <div className="form-group">
                <label>Capacity</label>
                <input 
                  type="number" 
                  name="capacity" 
                  value={newFacility.capacity} 
                  onChange={handleInputChange} 
                  required
                />
              </div>
              <div className="form-group">
                <label>Description</label>
                <textarea 
                  name="description" 
                  value={newFacility.description || ''} 
                  onChange={handleInputChange} 
                  rows="3"
                ></textarea>
              </div>

              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Add Facility</button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={() => setShowAddModal(false)}
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      
      {/* Edit Facility Modal */}
      {showEditModal && editFacility && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Edit Facility</h3>
            <form onSubmit={handleUpdateSubmit}>
              <div className="form-group">
                <label>Facility Name</label>
                <input 
                  type="text" 
                  name="name" 
                  value={editFacility.name} 
                  onChange={handleEditInputChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Campus</label>
                <select 
                  name="location" 
                  value={editFacility.location} 
                  onChange={handleEditInputChange} 
                  required
                >
                  <option value="">Select Campus</option>
                  <option value="Main Campus">Main Campus</option>
                  <option value="East Campus">East Campus</option>
                  <option value="West Campus">West Campus</option>
                  <option value="North Campus">North Campus</option>
                  <option value="South Campus">South Campus</option>
                </select>
              </div>
              <div className="form-group">
                <label>Capacity</label>
                <input 
                  type="number" 
                  name="capacity" 
                  value={editFacility.capacity} 
                  onChange={handleEditInputChange} 
                  required
                />
              </div>
              <div className="form-group">
                <label>Description</label>
                <textarea 
                  name="description" 
                  value={editFacility.description || ''} 
                  onChange={handleEditInputChange} 
                  rows="3"
                ></textarea>
              </div>

              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Update Facility</button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={() => setShowEditModal(false)}
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
}

export default ManageFacilities;