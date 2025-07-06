import React, { useState, useEffect } from 'react';
import './approverRequests.css';

const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const ApproverRequests = () => {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedRequest, setSelectedRequest] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [declineReason, setDeclineReason] = useState('');

  useEffect(() => {
    fetchRequests();
  }, []);

  const fetchRequests = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/get_requests.php`);
      const data = await response.json();
      if (data.success) {
        setRequests(data.requests || []);
      }
      setLoading(false);
    } catch (error) {
      console.error('Error fetching requests:', error);
      setLoading(false);
    }
  };

  const handleApprove = async (request) => {
    try {
      const response = await fetch(`${API_BASE_URL}/approve_request.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          id: request.id,
          approved_by: 'Approver'
        }),
        mode: 'no-cors'
      });

      // Remove from pending requests
      setRequests(prev => prev.filter(r => r.id !== request.id));
      alert('Request approved successfully!');
    } catch (error) {
      console.error('Error approving request:', error);
      alert('Error approving request. Please try again.');
    }
  };

  const handleDecline = async () => {
    if (!declineReason.trim()) {
      alert('Please provide a reason for declining.');
      return;
    }

    try {
      const response = await fetch(`${API_BASE_URL}/decline_request.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          id: selectedRequest.id,
          reason: declineReason,
          rejected_by: 'Approver'
        }),
        mode: 'no-cors'
      });

      // Remove from pending requests
      setRequests(prev => prev.filter(r => r.id !== selectedRequest.id));
      setShowModal(false);
      setSelectedRequest(null);
      setDeclineReason('');
      alert('Request declined successfully!');
    } catch (error) {
      console.error('Error declining request:', error);
      alert('Error declining request. Please try again.');
    }
  };

  const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const formattedHour = hour % 12 || 12;
    return `${formattedHour}:${minutes} ${ampm}`;
  };

  return (
    <div className="approver-requests">
      <h1 className="page-title">PENDING REQUESTS</h1>

      {loading ? (
        <p>Loading requests...</p>
      ) : requests.length > 0 ? (
        <div className="requests-grid">
          {requests.map(request => (
            <div key={request.id} className="request-card">
              <div className="request-header">
                <h3>{request.activity}</h3>
                <span className="reference-number">{request.reference_number}</span>
              </div>
              
              <div className="request-details">
                <p><strong>Requested by:</strong> {request.request_by}</p>
                <p><strong>Department:</strong> {request.department_organization}</p>
                <p><strong>Date:</strong> {new Date(request.date_need_from).toLocaleDateString()} - {new Date(request.date_need_until).toLocaleDateString()}</p>
                <p><strong>Time:</strong> {formatTime(request.start_time)} - {formatTime(request.end_time)}</p>
                <p><strong>Venue:</strong> {request.venue}</p>
                <p><strong>Purpose:</strong> {request.purpose}</p>
                <p><strong>Participants:</strong> {request.total_attendees || (request.total_male_attendees + request.total_female_attendees)}</p>
                {request.equipments_needed && (
                  <p><strong>Equipment:</strong> {request.equipments_needed}</p>
                )}
              </div>

              <div className="request-actions">
                <button 
                  className="approve-btn"
                  onClick={() => handleApprove(request)}
                >
                  Approve
                </button>
                <button 
                  className="decline-btn"
                  onClick={() => {
                    setSelectedRequest(request);
                    setShowModal(true);
                  }}
                >
                  Decline
                </button>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <p>No pending requests found.</p>
      )}

      {/* Decline Modal */}
      {showModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Decline Request</h3>
            <p>Please provide a reason for declining this request:</p>
            <textarea
              value={declineReason}
              onChange={(e) => setDeclineReason(e.target.value)}
              placeholder="Enter reason for declining..."
              rows="4"
            />
            <div className="modal-buttons">
              <button className="submit-btn" onClick={handleDecline}>
                Decline Request
              </button>
              <button 
                className="cancel-btn"
                onClick={() => {
                  setShowModal(false);
                  setSelectedRequest(null);
                  setDeclineReason('');
                }}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ApproverRequests;