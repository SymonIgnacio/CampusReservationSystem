import React, { useState, useEffect } from 'react';
import './vpoRequests.css';

function VPORequests() {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [approvedEvents, setApprovedEvents] = useState([]);
  const [showDetailsModal, setShowDetailsModal] = useState(false);
  const [selectedRequest, setSelectedRequest] = useState(null);

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
                <tr 
                  key={request.id}
                  onClick={() => {
                    setSelectedRequest(request);
                    setShowDetailsModal(true);
                  }}
                  style={{cursor: 'pointer'}}
                >
                  <td>{request.reference_number}</td>
                  <td>{request.activity}</td>
                  <td>{request.department_organization}</td>
                  <td>{request.date_need_from}</td>
                  <td>{request.venue}</td>
                  <td onClick={(e) => e.stopPropagation()}>
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
      
      {/* Request Details Modal */}
      {showDetailsModal && selectedRequest && (
        <div className="modal-overlay" onClick={() => setShowDetailsModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
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
                <div><strong>Nature:</strong> {selectedRequest.nature_of_activity}</div>
                <div><strong>Date From:</strong> {selectedRequest.date_need_from}</div>
                <div><strong>Date Until:</strong> {selectedRequest.date_need_until}</div>
                <div><strong>Time:</strong> {selectedRequest.start_time} - {selectedRequest.end_time}</div>
                <div><strong>Venue:</strong> {selectedRequest.venue}</div>
                <div><strong>Participants:</strong> {selectedRequest.participants || 'Not specified'}</div>
                <div><strong>Male Attendees:</strong> {selectedRequest.total_male_attendees || 0}</div>
                <div><strong>Female Attendees:</strong> {selectedRequest.total_female_attendees || 0}</div>
                <div><strong>Total Attendees:</strong> {selectedRequest.total_attendees || 0}</div>
                <div><strong>Equipment:</strong> {selectedRequest.equipments_needed || 'None'}</div>
                <div><strong>Requested By:</strong> {selectedRequest.request_by}</div>
                <div><strong>Date Created:</strong> {selectedRequest.date_created}</div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default VPORequests;