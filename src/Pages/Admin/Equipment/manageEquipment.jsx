import React, { useState, useEffect } from 'react';
import './manageEquipment.css';

function ManageEquipment() {
  const [equipment, setEquipment] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showAddModal, setShowAddModal] = useState(false);
  const [newEquipment, setNewEquipment] = useState({
    name: '',
    stock: ''
  });

  // Fetch equipment on component mount
  useEffect(() => {
    fetchEquipment();
  }, []);

  // Fetch equipment from API
  const fetchEquipment = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/equipment.php', {
        credentials: 'include',
        mode: 'cors'
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        setEquipment(data.equipment || []);
      } else {
        throw new Error(data.message || 'Failed to fetch equipment');
      }
    } catch (error) {
      console.error('Error fetching equipment:', error);
      setError('Failed to load equipment. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Handle input change for new equipment form
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewEquipment({
      ...newEquipment,
      [name]: value
    });
  };

  // Handle form submission for adding or updating equipment
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const isUpdate = !!newEquipment.id;
      const url = isUpdate 
        ? 'http://localhost/CampusReservationSystem/src/api/update_equipment_item.php'
        : 'http://localhost/CampusReservationSystem/src/api/add_equipment_item.php';
      
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        mode: 'cors',
        body: JSON.stringify(newEquipment),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Reset form and close modal
        setNewEquipment({
          name: '',
          stock: ''
        });
        setShowAddModal(false);
        
        // Refresh equipment list
        fetchEquipment();
        
        alert(isUpdate ? 'Equipment updated successfully!' : 'Equipment added successfully!');
      } else {
        alert(`Failed to ${isUpdate ? 'update' : 'add'} equipment: ${data.message}`);
      }
    } catch (error) {
      console.error(`Error ${newEquipment.id ? 'updating' : 'adding'} equipment:`, error);
      alert(`Error ${newEquipment.id ? 'updating' : 'adding'} equipment: ${error.message}`);
    }
  };

  // Handle delete equipment
  const handleDelete = async (equipmentId) => {
    if (!window.confirm('Are you sure you want to delete this equipment?')) {
      return;
    }
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/delete_equipment_item.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        mode: 'cors',
        body: JSON.stringify({ equipmentId }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Remove equipment from state
        setEquipment(equipment.filter(item => item.equipment_id !== equipmentId));
        alert('Equipment deleted successfully!');
      } else {
        alert(`Failed to delete equipment: ${data.message}`);
      }
    } catch (error) {
      console.error('Error deleting equipment:', error);
      alert(`Error deleting equipment: ${error.message}`);
    }
  };

  return (
    <div className="manage-equipment-container">
      <h1 className="page-title">MANAGE EQUIPMENT</h1>
      
      <div className="controls">
        <button 
          className="add-equipment-button"
          onClick={() => setShowAddModal(true)}
        >
          Add New Equipment
        </button>
      </div>
      
      {loading ? (
        <div className="loading">Loading equipment...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : equipment.length === 0 ? (
        <div className="no-equipment">No equipment found.</div>
      ) : (
        <div className="equipment-table-container">
          <table className="equipment-table">
            <thead>
              <tr>
                <th>Equipment Name</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {equipment.map(item => (
                <tr key={item.equipment_id}>
                  <td>{item.name}</td>
                  <td>{item.stock}</td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="edit-btn"
                        onClick={() => {
                          setNewEquipment({
                            id: item.equipment_id,
                            name: item.name,
                            stock: item.stock
                          });
                          setShowAddModal(true);
                        }}
                      >
                        Edit
                      </button>
                      <button 
                        className="delete-btn"
                        onClick={() => handleDelete(item.equipment_id)}
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
      
      {/* Add/Edit Equipment Modal */}
      {showAddModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>{newEquipment.id ? 'Edit Equipment' : 'Add New Equipment'}</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Equipment Name</label>
                <input 
                  type="text" 
                  name="name" 
                  value={newEquipment.name} 
                  onChange={handleInputChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Stock</label>
                <input 
                  type="number" 
                  name="stock" 
                  value={newEquipment.stock} 
                  onChange={handleInputChange} 
                  required
                  min="0"
                />
              </div>

              <div className="modal-buttons">
                <button type="submit" className="submit-btn">
                  {newEquipment.id ? 'Update Equipment' : 'Add Equipment'}
                </button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={() => {
                    setNewEquipment({
                      name: '',
                      stock: ''
                    });
                    setShowAddModal(false);
                  }}
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

export default ManageEquipment;