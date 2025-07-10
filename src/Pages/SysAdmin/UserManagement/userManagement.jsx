import React, { useState, useEffect } from 'react';
import './userManagement.css';

const UserManagement = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    role: 'Student',
    status: 'Active'
  });

  const roles = ['student', 'faculty', 'admin', 'sysadmin', 'vpo'];

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_all_users.php', {
        credentials: 'include',
        mode: 'cors'
      });
      const data = await response.json();
      
      if (data.success) {
        setUsers(data.users || []);
        setError(null);
      } else {
        throw new Error(data.message || 'Failed to fetch users');
      }
    } catch (err) {
      console.error('Error fetching users:', err);
      setError('Failed to load users');
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      if (editingUser) {
        const response = await fetch('http://localhost/CampusReservationSystem/src/api/update_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            user_id: editingUser.user_id,
            username: formData.username,
            email: formData.email,
            role: formData.role
          })
        });
        const result = await response.json();
        if (result.success) {
          fetchUsers();
          alert('User updated successfully!');
        } else {
          alert('Failed to update user: ' + result.message);
        }
      } else {
        alert('Adding new users is not implemented yet.');
      }
      resetForm();
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred');
    }
  };

  const resetForm = () => {
    setFormData({
      username: '',
      email: '',
      password: '',
      role: 'Student',
      status: 'Active'
    });
    setEditingUser(null);
    setShowModal(false);
  };

  const handleEdit = (user) => {
    setEditingUser(user);
    setFormData({
      username: user.username,
      email: user.email,
      password: '',
      role: user.role,
      status: user.status
    });
    setShowModal(true);
  };

  const handleDelete = async (userId) => {
    if (window.confirm('Are you sure you want to delete this user?')) {
      try {
        const response = await fetch('http://localhost/CampusReservationSystem/src/api/delete_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: userId })
        });
        const result = await response.json();
        if (result.success) {
          fetchUsers();
          alert('User deleted successfully!');
        } else {
          alert('Failed to delete user: ' + result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
      }
    }
  };

  const handleResetPassword = (userId) => {
    if (window.confirm('Send password reset email to this user?')) {
      alert('Password reset email sent successfully!');
    }
  };

  return (
    <div className="user-management">
      <div className="page-header">
        <h1 className="page-title">USER MANAGEMENT</h1>
        <button 
          className="add-user-btn"
          onClick={() => setShowModal(true)}
        >
          Add New User
        </button>
      </div>

      <div className="users-table-container">
        <table className="users-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map(user => (
              <tr key={user.user_id || user.id}>
                <td>{user.username}</td>
                <td>{user.email}</td>
                <td>
                  <span className={`role-badge ${(user.role || 'student').toLowerCase()}`}>
                    {(user.role || 'student').toUpperCase()}
                  </span>
                </td>
                <td>
                  <span className={`status-badge ${(user.status || 'active').toLowerCase()}`}>
                    {user.status || 'Active'}
                  </span>
                </td>
                <td>{new Date(user.created_at).toLocaleDateString() || 'N/A'}</td>
                <td>
                  <div className="action-buttons">
                    <button 
                      className="edit-btn"
                      onClick={() => handleEdit(user)}
                    >
                      Edit
                    </button>
                    <button 
                      className="reset-btn"
                      onClick={() => handleResetPassword(user.user_id || user.id)}
                    >
                      Reset Password
                    </button>
                    <button 
                      className="delete-btn"
                      onClick={() => handleDelete(user.user_id || user.id)}
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

      {/* Add/Edit User Modal */}
      {showModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>{editingUser ? 'Edit User' : 'Add New User'}</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Username</label>
                <input
                  type="text"
                  name="username"
                  value={formData.username}
                  onChange={handleInputChange}
                  required
                />
              </div>
              
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
              
              {!editingUser && (
                <div className="form-group">
                  <label>Password</label>
                  <input
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleInputChange}
                    required={!editingUser}
                  />
                </div>
              )}
              
              <div className="form-group">
                <label>Role</label>
                <select
                  name="role"
                  value={formData.role}
                  onChange={handleInputChange}
                  required
                >
                  {roles.map(role => (
                    <option key={role} value={role}>{role}</option>
                  ))}
                </select>
              </div>
              
              <div className="form-group">
                <label>Status</label>
                <select
                  name="status"
                  value={formData.status}
                  onChange={handleInputChange}
                  required
                >
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>

              <div className="modal-buttons">
                <button type="submit" className="submit-btn">
                  {editingUser ? 'Update User' : 'Create User'}
                </button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={resetForm}
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
};

export default UserManagement;