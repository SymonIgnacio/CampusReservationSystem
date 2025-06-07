import React, { createContext, useState, useEffect } from 'react';

export const RequestContext = createContext();

export const RequestProvider = ({ children }) => {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchRequests = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_requests.php');
      const data = await response.json();
      
      if (data.success) {
        setRequests(data.requests || []);
      } else {
        setError(data.message || 'Failed to fetch requests');
      }
    } catch (err) {
      console.error('Error fetching requests:', err);
      setError('Network error. Please check your connection.');
    } finally {
      setLoading(false);
    }
  };

  const updateRequestStatus = async (id, status, reason = null) => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/update_request_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id, status, reason }),
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Update local state
        setRequests(prev => prev.map(req => 
          req.id === id ? { ...req, status } : req
        ));
        return { success: true };
      } else {
        return { success: false, message: result.message };
      }
    } catch (err) {
      console.error('Error updating request status:', err);
      return { success: false, message: err.message };
    }
  };

  // Initial fetch
  useEffect(() => {
    fetchRequests();
  }, []);

  return (
    <RequestContext.Provider value={{
      requests,
      loading,
      error,
      fetchRequests,
      updateRequestStatus
    }}>
      {children}
    </RequestContext.Provider>
  );
};

export default RequestProvider;