import React, { useState, useEffect } from 'react';
import './ViewFacilities.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ViewFacilities = () => {
  const [facilities, setFacilities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchFacilities();
  }, []);

  const fetchFacilities = async () => {
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/get_facilities.php`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        setFacilities(data.facilities || []);
        setError(null);
      } else {
        throw new Error(data.message || 'Failed to fetch facilities');
      }
    } catch (err) {
      console.error('Error fetching facilities:', err);
      setError(`Failed to load facilities: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="view-facilities-container">
      <h1 className="page-title">FACILITIES</h1>
      
      <div className="controls">
        <button 
          className="refresh-button"
          onClick={fetchFacilities}
          disabled={loading}
        >
          {loading ? 'Loading...' : 'Refresh'}
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}

      {loading ? (
        <div className="loading-message">Loading facilities...</div>
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
              </tr>
            </thead>
            <tbody>
              {facilities.map(facility => (
                <tr key={facility.resource_id}>
                  <td>{facility.name}</td>
                  <td>{facility.campus || 'N/A'}</td>
                  <td>{facility.capacity || 'N/A'}</td>
                  <td>{facility.description || 'N/A'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default ViewFacilities;