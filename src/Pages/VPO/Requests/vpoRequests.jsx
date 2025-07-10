import React, { useState, useEffect } from 'react';

function VPORequests() {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [approvedEvents, setApprovedEvents] = useState([]);

  useEffect(() => {
    fetchRequests();
    fetchApprovedEvents();
  }, []);

  const fetchApprovedEvents = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/admin_dashboard_approved_events.php');
      const data = await response.json();
      if (data.success) {
        setApprovedEvents(data.events || []);
      }
    } catch (error) {
      console.error('Error fetching approved events:', error);
    }
  };

  const checkScheduleConflict = (request) => {
    return approvedEvents.some(event => {
      const eventVenue = event.venue || event.venue_name;
      const requestVenue = request.venue;
      
      if (eventVenue !== requestVenue) return false;
      
      const eventStart = new Date(event.date_need_from);
      const eventEnd = new Date(event.date_need_until);
      const requestStart = new Date(request.date_need_from);
      const requestEnd = new Date(request.date_need_until);
      
      return requestStart <= eventEnd && requestEnd >= eventStart;
    });
  };

  const fetchRequests = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_vpo_requests.php');
      const data = await response.json();
      if (data.success) {
        setRequests(data.requests || []);
      }
    } catch (error) {
      console.error('Error fetching requests:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (requestId) => {
    if (window.confirm('Give final VPO approval to this request?')) {
      try {
        const response = await fetch('http://localhost/CampusReservationSystem/src/api/approve_request.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          mode: 'cors',
          body: JSON.stringify({ request_id: requestId, approvedBy: 'VPO' })
        });
        const result = await response.json();
        if (result.success) {
          setRequests(prev => prev.filter(req => req.id !== requestId));
          alert('Request approved successfully');
        }
      } catch (error) {
        console.error('Error approving request:', error);
      }
    }
  };

  const handleDecline = async (requestId) => {
    const declineReason = prompt('Reason for declining:');
    if (declineReason) {
      try {
        const response = await fetch('http://localhost/CampusReservationSystem/src/api/decline_request.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          mode: 'cors',
          body: JSON.stringify({ request_id: requestId, decline_reason: declineReason })
        });
        const result = await response.json();
        if (result.success) {
          setRequests(prev => prev.filter(req => req.id !== requestId));
          alert('Request declined successfully');
        }
      } catch (error) {
        console.error('Error declining request:', error);
      }
    }
  };

  return (
    <div className="vpo-requests">
      <h2>PENDING VPO APPROVAL</h2>
      
      {loading ? (
        <div>Loading...</div>
      ) : requests.length === 0 ? (
        <div>No requests pending VPO approval</div>
      ) : (
        <div className="requests-table-container">
          <table className="requests-table">
            <thead>
              <tr>
                <th>Reference</th>
                <th>Activity</th>
                <th>Department</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {requests.map(request => (
                <tr key={request.id}>
                  <td>{request.reference_number}</td>
                  <td>{request.activity}</td>
                  <td>{request.department_organization}</td>
                  <td>{request.date_need_from}</td>
                  <td>{request.venue}</td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="approve-btn"
                        onClick={() => handleApprove(request.id)}
                      >
                        VPO Approve
                      </button>
                      <button 
                        className="decline-btn"
                        onClick={() => handleDecline(request.id)}
                      >
                        Decline
                      </button>
                    </div>
                    {checkScheduleConflict(request) && (
                      <div style={{color: '#ff9800', fontSize: '12px', marginTop: '4px'}}>
                        ⚠️ Schedule Conflict
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default VPORequests;