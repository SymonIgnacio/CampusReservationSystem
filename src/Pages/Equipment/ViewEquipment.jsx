import React, { useState, useEffect } from 'react';
import './ViewEquipment.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ViewEquipment = () => {
  const [equipment, setEquipment] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchEquipment();
  }, []);

  const fetchEquipment = async () => {
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/equipment.php`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        setEquipment(data.equipment || []);
        setError(null);
      } else {
        throw new Error(data.message || 'Failed to fetch equipment');
      }
    } catch (err) {
      console.error('Error fetching equipment:', err);
      setError(`Failed to load equipment: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="view-equipment-container">
      <h1 className="page-title">EQUIPMENT</h1>
      
      <div className="controls">
        <button 
          className="refresh-button"
          onClick={fetchEquipment}
          disabled={loading}
        >
          {loading ? 'Loading...' : 'Refresh'}
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}

      {loading ? (
        <div className="loading-message">Loading equipment...</div>
      ) : equipment.length === 0 ? (
        <div className="no-equipment">No equipment found.</div>
      ) : (
        <div className="equipment-table-container">
          <table className="equipment-table">
            <thead>
              <tr>
                <th>Equipment Name</th>
                <th>Available Stock</th>
              </tr>
            </thead>
            <tbody>
              {equipment.map(item => (
                <tr key={item.equipment_id}>
                  <td>{item.name}</td>
                  <td>{item.stock}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default ViewEquipment;