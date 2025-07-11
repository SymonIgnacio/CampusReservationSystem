import React, { useState, useEffect } from 'react';
import './adminRequests.css';

function AdminRequests() {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showViewModal, setShowViewModal] = useState(false);
  const [showDeclineModal, setShowDeclineModal] = useState(false);
  const [showDeclineReasonModal, setShowDeclineReasonModal] = useState(false);
  const [showApproveModal, setShowApproveModal] = useState(false);
  const [currentRequest, setCurrentRequest] = useState(null);
  const [processingId, setProcessingId] = useState(null);
  const [requestToDecline, setRequestToDecline] = useState(null);
  const [declineReason, setDeclineReason] = useState('');
  const [approvedEvents, setApprovedEvents] = useState([]);

  // Fetch requests
  const fetchRequests = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_requests.php', {
        mode: 'cors'
      });
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

  // Initial fetch
  useEffect(() => {
    fetchRequests();
    fetchApprovedEvents();
  }, []);

  const fetchApprovedEvents = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/admin_dashboard_approved_events.php', {
        mode: 'cors'
      });
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

  // Format date
  const formatDate = (dateStr) => {
    if (!dateStr) return 'Date TBD';
    try {
      const date = new Date(dateStr);
      if (isNaN(date.getTime())) return dateStr;
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    } catch (error) {
      return dateStr;
    }
  };
  
  // Open approve confirmation modal
  const openApproveModal = (id) => {
    const request = requests.find(req => req.id === id);
    setCurrentRequest(request);
    setShowApproveModal(true);
  };
  
  // Handle approve request
  const handleApprove = async () => {
    if (!currentRequest) {
      alert('No request selected to approve');
      return;
    }
    
    setProcessingId(currentRequest.id);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/approve_request.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        mode: 'cors',
        body: JSON.stringify({ 
          request_id: currentRequest.id, 
          approvedBy: 'Admin' // This could be replaced with actual admin name from session
        }),
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Remove the request from the local state
        setRequests(prev => prev.filter(req => req.id !== currentRequest.id));
        // Close the modal
        setShowApproveModal(false);
        alert('Request approved successfully');
      } else {
        alert('Failed to approve request: ' + result.message);
      }
    } catch (error) {
      console.error('Error approving request:', error);
      alert('Error approving request');
    } finally {
      setProcessingId(null);
    }
  };

  // Open decline confirmation modal
  const openDeclineModal = (id) => {
    const request = requests.find(req => req.id === id);
    setRequestToDecline(request);
    setShowDeclineModal(true);
  };
  
  // Open decline reason modal after confirmation
  const openDeclineReasonModal = () => {
    setShowDeclineModal(false);
    setShowDeclineReasonModal(true);
    setDeclineReason('');
  };
  
  // Handle decline request with reason
  const handleDeclineWithReason = async () => {
    if (!requestToDecline) {
      alert('No request selected to decline');
      return;
    }
    
    if (!declineReason.trim()) {
      alert('Please provide a reason for declining this request');
      return;
    }
    
    setProcessingId(requestToDecline.id);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/decline_request.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        mode: 'cors',
        body: JSON.stringify({ 
          request_id: requestToDecline.id, 
          decline_reason: declineReason
        }),
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Remove the request from the local state since it's deleted from the database
        setRequests(prev => prev.filter(req => req.id !== requestToDecline.id));
        // Close the modal
        setShowDeclineReasonModal(false);
        alert('Request declined successfully');
      } else {
        alert('Failed to decline request: ' + result.message);
      }
    } catch (error) {
      console.error('Error declining request:', error);
      alert('Error declining request');
    } finally {
      setProcessingId(null);
    }
  };

  return (
    <div className="admin-requests-container">
      <h2 className="admin-request-page-title">RESERVATION REQUESTS</h2>
      
      <div className="admin-requests-controls">
        <button 
          className="refresh-button"
          onClick={fetchRequests}
          disabled={loading}
        >
          {loading ? 'Loading...' : 'Refresh'}
        </button>
      </div>
      
      {loading ? (
        <div className="loading">Loading requests...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : requests.length === 0 ? (
        <div className="no-requests">No requests found.</div>
      ) : (
        <div className="requests-table-container">
          <table className="requests-table">
            <thead>
              <tr>
                <th>Ref #</th>
                <th>Venue</th>
                <th>Dep/Org</th>
                <th>Date</th>
                <th>Time</th>
                <th>Requested By</th>
                <th>Details</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {requests.map(request => (
                <tr key={request.id} className={`status-${request.status}`}>
                  <td>{request.reference_number}</td>
                  <td>{request.venue}</td>
                  <td>{request.department_organization}</td>
                  <td>{formatDate(request.date_created)}</td>
                  <td>{request.date_created ? new Date(request.date_created).toLocaleTimeString() : 'N/A'}</td>
                  <td>{request.request_by}</td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="view-btn"
                        onClick={() => {
                          setCurrentRequest(request);
                          setShowViewModal(true);
                        }}
                      >
                        View
                      </button>
                    </div>
                  </td>
                  <td>
                    <span className={`status-badge ${(request.status || 'pending-gso').replace('_', '-')}`}>
                      {request.status === 'pending_gso' ? 'Pending (GSO)' :
                       request.status === 'pending_vpo' ? 'Pending (VPO)' :
                       request.status === 'declined_gso' ? 'Declined by GSO' :
                       request.status === 'declined_vpo' ? 'Declined by VPO' :
                       (!request.status || request.status === '') ? 'Pending (GSO)' :
                       request.status.toUpperCase()}
                    </span>
                    {checkScheduleConflict(request) && (
                      <div style={{color: '#ff9800', fontSize: '12px', marginTop: '4px'}}>
                        ⚠️ Schedule Conflict
                      </div>
                    )}
                  </td>
                  <td>
                    {(request.status === 'pending_gso' || request.status === 'pending_vpo' || !request.status || request.status === '') && (
                      <div className="action-buttons">
                        <button 
                          className="approve-btn"
                          onClick={() => openApproveModal(request.id)}
                          disabled={processingId === request.id}
                        >
                          {processingId === request.id ? 'Processing...' : 
                           (request.status === 'pending_vpo' ? 'VPO Approve' : 'GSO Approve')}
                        </button>
                        <button 
                          className="decline-btn"
                          onClick={() => openDeclineModal(request.id)}
                          disabled={processingId === request.id}
                        >
                          {processingId === request.id ? 'Processing...' : 'Decline'}
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Decline Confirmation Modal */}
      {showDeclineModal && requestToDecline && (
        <div className="modal-overlay">
          <div className="admin-requests-modal-content">
            <h3>Decline Request</h3>
            <p>Are you sure you want to decline this request?</p>
            <div className="modal-buttons">
              <button 
                className="modal-submit" 
                onClick={openDeclineReasonModal}
                disabled={processingId === requestToDecline.id}
              >
                {processingId === requestToDecline.id ? 'Processing...' : 'Yes, Decline'}
              </button>
              <button 
                className="modal-cancel" 
                onClick={() => setShowDeclineModal(false)}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
      
      {/* Decline Reason Modal */}
      {showDeclineReasonModal && requestToDecline && (
        <div className="modal-overlay">
          <div className="admin-requests-modal-content">
            <h3>Provide Reason for Declining</h3>
            <p>Please provide a reason why this request is being declined:</p>
            <textarea
              className="decline-reason-textarea"
              value={declineReason}
              onChange={(e) => setDeclineReason(e.target.value)}
              placeholder="Enter reason for declining this request..."
              rows={4}
            />
            <div className="modal-buttons">
              <button 
                className="modal-submit" 
                onClick={handleDeclineWithReason}
                disabled={processingId === requestToDecline.id || !declineReason.trim()}
              >
                {processingId === requestToDecline.id ? 'Processing...' : 'Submit'}
              </button>
              <button 
                className="modal-cancel" 
                onClick={() => setShowDeclineReasonModal(false)}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Approve Confirmation Modal */}
      {showApproveModal && currentRequest && (
        <div className="modal-overlay">
          <div className="admin-requests-modal-content">
            <h3>Approve Request</h3>
            <p>Are you sure you want to approve this request?</p>
            <div className="modal-buttons">
              <button 
                className="modal-submit" 
                onClick={handleApprove}
                disabled={processingId === currentRequest.id}
              >
                {processingId === currentRequest.id ? 'Processing...' : 'Yes, Approve'}
              </button>
              <button 
                className="modal-cancel" 
                onClick={() => setShowApproveModal(false)}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}

      {/* View Request Modal */}
      {showViewModal && currentRequest && (
        <div className="modal-overlay">
          <div className="admin-requests-modal-content view-modal">
            <h3>Request Details</h3>
            <div className="request-details">
              <div className="detail-row">
                <span className="detail-label">Reference Number:</span>
                <span className="detail-value">{currentRequest.reference_number}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Date Created:</span>
                <span className="detail-value">{formatDate(currentRequest.date_created)}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Requested By:</span>
                <span className="detail-value">{currentRequest.request_by}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Department:</span>
                <span className="detail-value">{currentRequest.department_organization}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Activity:</span>
                <span className="detail-value">{currentRequest.activity}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Purpose:</span>
                <span className="detail-value">{currentRequest.purpose}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Nature of Activity:</span>
                <span className="detail-value">{currentRequest.nature_of_activity}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Date Needed:</span>
                <span className="detail-value">
                  {formatDate(currentRequest.date_need_from)} to {formatDate(currentRequest.date_need_until)}
                </span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Time Needed:</span>
                <span className="detail-value">
                  {currentRequest.start_time} to {currentRequest.end_time}
                </span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Participants:</span>
                <span className="detail-value">{currentRequest.participants || 'N/A'}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Attendees:</span>
                <span className="detail-value">
                  Male: {currentRequest.total_male_attendees}, 
                  Female: {currentRequest.total_female_attendees}, 
                  Total: {currentRequest.total_attendees}
                </span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Venue:</span>
                <span className="detail-value">{currentRequest.venue}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Equipment:</span>
                <span className="detail-value">{currentRequest.equipments_needed || 'None'}</span>
              </div>
              <div className="detail-row">
                <span className="detail-label">Status:</span>
                <span className={`detail-value status-badge ${currentRequest.status}`}>
                  {currentRequest.status.toUpperCase()}
                </span>
              </div>
            </div>
            <div className="modal-buttons">
              <button 
                className="modal-cancel" 
                onClick={() => setShowViewModal(false)}
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminRequests;