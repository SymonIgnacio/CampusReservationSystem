import React, { useState, useEffect } from 'react';
import './manageUsers.css';

function ManageUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showEditModal, setShowEditModal] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [formData, setFormData] = useState({
    firstname: '',
    middlename: '',
    lastname: '',
    department: '',
    email: '',
    role: ''
  });

  // Fetch users on component mount
  useEffect(() => {
    fetchUsers();
  }, []);

  // Fetch users from API
  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/users.php');
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        setUsers(data.users || []);
      } else {
        throw new Error(data.message || 'Failed to fetch users');
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      setError('Failed to load users. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Handle input change for edit user form
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
  };

  // Open edit modal with user data
  const handleEditClick = (user) => {
    setCurrentUser(user);
    setFormData({
      firstname: user.firstname,
      middlename: user.middlename || '',
      lastname: user.lastname,
      department: user.department || '',
      email: user.email,
      role: user.role
    });
    setShowEditModal(true);
  };

  // Handle form submission for editing user
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/update_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: currentUser.user_id,
          ...formData
        }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Close modal
        setShowEditModal(false);
        
        // Refresh users list
        fetchUsers();
        
        alert('User updated successfully!');
      } else {
        alert(`Failed to update user: ${data.message}`);
      }
    } catch (error) {
      console.error('Error updating user:', error);
      alert(`Error updating user: ${error.message}`);
    }
  };

  // Handle delete user
  const handleDelete = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user?')) {
      return;
    }
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/delete_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Remove user from state
        setUsers(users.filter(user => user.user_id !== userId));
        alert('User deleted successfully!');
      } else {
        alert(`Failed to delete user: ${data.message}`);
      }
    } catch (error) {
      console.error('Error deleting user:', error);
      alert(`Error deleting user: ${error.message}`);
    }
  };

  return (
    <div className="manage-users-container">
      <h1 className="page-title">MANAGE USERS</h1>
      
      {loading ? (
        <div className="loading">Loading users...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : users.length === 0 ? (
        <div className="no-users">No users found.</div>
      ) : (
        <div className="users-table-container">
          <table className="users-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.user_id}>
                  <td>{`${user.firstname} ${user.middlename ? user.middlename + ' ' : ''}${user.lastname}`}</td>
                  <td>{user.username}</td>
                  <td>{user.email}</td>
                  <td>{user.department || 'N/A'}</td>
                  <td>{user.role}</td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="edit-btn"
                        onClick={() => handleEditClick(user)}
                      >
                        Edit
                      </button>
                      <button 
                        className="delete-btn"
                        onClick={() => handleDelete(user.user_id)}
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
      
      {/* Edit User Modal */}
      {showEditModal && currentUser && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Edit User</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-row">
                <div className="form-group">
                  <label>First Name</label>
                  <input 
                    type="text" 
                    name="firstname" 
                    value={formData.firstname} 
                    onChange={handleInputChange} 
                    required 
                  />
                </div>
                <div className="form-group">
                  <label>Middle Name</label>
                  <input 
                    type="text" 
                    name="middlename" 
                    value={formData.middlename} 
                    onChange={handleInputChange} 
                  />
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Last Name</label>
                  <input 
                    type="text" 
                    name="lastname" 
                    value={formData.lastname} 
                    onChange={handleInputChange} 
                    required 
                  />
                </div>
                <div className="form-group">
                  <label>Department</label>
                  <select
                    name="department"
                    value={formData.department}
                    onChange={handleInputChange}
                  >
                    <option value="">Select Department</option>
                    <option value="College of Computer Studies">College of Computer Studies</option>
                    <option value="College of Accountancy">College of Accountancy</option>
                    <option value="College of Arts And Science">College of Arts And Science</option>
                    <option value="College Of Education">College Of Education</option>
                    <option value="College of Hospitality Management and Tourism">College of Hospitality Management and Tourism</option>
                    <option value="College Of Business Administration">College Of Business Administration</option>
                    <option value="College of Health and Sciences">College of Health and Sciences</option>
                    <option value="School of Psychology">School of Psychology</option>
                    <option value="College of Maritime Education">College of Maritime Education</option>
                    <option value="School of Mechanical Engineering">School of Mechanical Engineering</option>
                  </select>
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Email</label>
                  <input 
                    type="email" 
                    name="email" 
                    value={formData.email} 
                    onChange={handleInputChange} 
                    required 
                  />
                </div>
                <div className="form-group">
                  <label>Role</label>
                  <select
                    name="role"
                    value={formData.role}
                    onChange={handleInputChange}
                    required
                  >
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
              </div>
              
              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Update User</button>
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

export default ManageUsers;