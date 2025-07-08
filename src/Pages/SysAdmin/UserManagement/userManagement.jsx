import React, { useState } from 'react';
import './userManagement.css';

const UserManagement = () => {
  const [users, setUsers] = useState([
    { id: 1, username: 'admin', email: 'admin@example.com', role: 'Admin', status: 'Active', lastLogin: '2025-01-07' },
    { id: 2, username: 'approver', email: 'approver@example.com', role: 'Approver', status: 'Active', lastLogin: '2025-01-06' },
    { id: 3, username: 'director1', email: 'director@example.com', role: 'Director', status: 'Active', lastLogin: '2025-01-05' }
  ]);

  const [showModal, setShowModal] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    role: 'Student',
    status: 'Active'
  });

  const roles = ['Student', 'Faculty', 'Approver', 'Admin', 'Director', 'Registrar'];

  const handleInputChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (editingUser) {
      setUsers(users.map(user => 
        user.id === editingUser.id 
          ? { ...user, ...formData, id: editingUser.id }
          : user
      ));
    } else {
      setUsers([...users, { 
        ...formData, 
        id: Date.now(),
        lastLogin: 'Never'
      }]);
    }
    resetForm();
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

  const handleDeactivate = (userId) => {
    if (window.confirm('Are you sure you want to deactivate this user?')) {
      setUsers(users.map(user => 
        user.id === userId 
          ? { ...user, status: user.status === 'Active' ? 'Inactive' : 'Active' }
          : user
      ));
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
              <tr key={user.id}>
                <td>{user.username}</td>
                <td>{user.email}</td>
                <td>
                  <span className={`role-badge ${user.role.toLowerCase()}`}>
                    {user.role}
                  </span>
                </td>
                <td>
                  <span className={`status-badge ${user.status.toLowerCase()}`}>
                    {user.status}
                  </span>
                </td>
                <td>{user.lastLogin}</td>
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
                      onClick={() => handleResetPassword(user.id)}
                    >
                      Reset Password
                    </button>
                    <button 
                      className={`toggle-btn ${user.status === 'Active' ? 'deactivate' : 'activate'}`}
                      onClick={() => handleDeactivate(user.id)}
                    >
                      {user.status === 'Active' ? 'Deactivate' : 'Activate'}
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