import React, { useState, useEffect } from 'react';
import './adminUsers.css';

function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [filteredUsers, setFilteredUsers] = useState([]);
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [newUser, setNewUser] = useState({
    firstname: '',
    lastname: '',
    email: '',
    username: '',
    password: '',
    role: 'student',
    department: ''
  });

  // Fetch users on component mount
  useEffect(() => {
    fetchUsers();
  }, []);

  // Filter users when search term or users change
  useEffect(() => {
    if (users.length > 0) {
      filterUsers();
    }
  }, [searchTerm, users]);

  // Fetch users from API
  const fetchUsers = async () => {
    setLoading(true);
    setError(null);
    try {
      // Use the combined users.php endpoint
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/users.php', {
        cache: 'no-cache' // Prevent caching
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const text = await response.text();
      
      try {
        const data = JSON.parse(text);
        if (data.success) {
          console.log('Users loaded:', data.users);
          setUsers(data.users || []);
          setFilteredUsers(data.users || []);
        } else {
          throw new Error(data.message || 'Failed to fetch users');
        }
      } catch (parseError) {
        console.error('JSON parse error:', parseError, 'Raw response:', text);
        throw new Error('Invalid response format from server');
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      setError('Failed to load users. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Filter users based on search term
  const filterUsers = () => {
    if (!searchTerm.trim()) {
      setFilteredUsers(users);
      return;
    }
    
    const filtered = users.filter(user => {
      const fullName = `${user.firstname} ${user.lastname}`.toLowerCase();
      return (
        fullName.includes(searchTerm.toLowerCase()) ||
        user.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.role.toLowerCase().includes(searchTerm.toLowerCase())
      );
    });
    
    setFilteredUsers(filtered);
  };

  // Handle search input change
  const handleSearchChange = (e) => {
    setSearchTerm(e.target.value);
  };

  // Handle search form submit
  const handleSearchSubmit = (e) => {
    e.preventDefault();
    filterUsers();
  };

  // Reset search
  const handleResetSearch = () => {
    setSearchTerm('');
    setFilteredUsers(users);
  };

  // Handle new user form input change
  const handleNewUserChange = (e) => {
    const { name, value } = e.target;
    setNewUser(prev => ({
      ...prev,
      [name]: value
    }));
  };

  // Handle add user form submit
  const handleAddUser = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/add_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(newUser),
      });
      
      const text = await response.text();
      
      try {
        const data = JSON.parse(text);
        if (data.success) {
          // Reset form and close modal
          setNewUser({
            firstname: '',
            lastname: '',
            email: '',
            username: '',
            password: '',
            role: 'student',
            department: ''
          });
          setShowAddUserModal(false);
          
          // Refresh users list
          fetchUsers();
          
          alert('User added successfully!');
        } else {
          alert(`Failed to add user: ${data.message}`);
        }
      } catch (parseError) {
        console.error('JSON parse error:', parseError, 'Raw response:', text);
        alert('Error adding user: Invalid response from server');
      }
    } catch (error) {
      console.error('Error adding user:', error);
      alert(`Error adding user: ${error.message}`);
    }
  };

  // Handle delete user
  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user?')) {
      return;
    }
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/delete_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ userId }),
      });
      
      const text = await response.text();
      
      try {
        const data = JSON.parse(text);
        if (data.success) {
          // Remove user from state
          setUsers(users.filter(user => user.user_id !== userId));
          alert('User deleted successfully!');
        } else {
          alert(`Failed to delete user: ${data.message}`);
        }
      } catch (parseError) {
        console.error('JSON parse error:', parseError, 'Raw response:', text);
        alert('Error deleting user: Invalid response from server');
      }
    } catch (error) {
      console.error('Error deleting user:', error);
      alert(`Error deleting user: ${error.message}`);
    }
  };

  // Get role badge class
  const getRoleBadgeClass = (role) => {
    switch (role.toLowerCase()) {
      case 'admin':
        return 'role-badge admin';
      case 'faculty':
        return 'role-badge faculty';
      case 'student':
        return 'role-badge student';
      default:
        return 'role-badge';
    }
  };

  return (
    <div className="admin-users-container">
      <h2 className="page-title">USER MANAGEMENT</h2>
      
      <div className="controls">
        <div className="search-container">
          <form onSubmit={handleSearchSubmit}>
            <input 
              type="text" 
              placeholder="Search users..." 
              value={searchTerm}
              onChange={handleSearchChange}
              className="search-input"
            />
            <button type="submit" className="search-button">Search</button>
            {searchTerm && (
              <button 
                type="button" 
                className="reset-button"
                onClick={handleResetSearch}
              >
                Reset
              </button>
            )}
          </form>
        </div>
        
        <div className="add-user-container">
          <button 
            className="add-user-button"
            onClick={() => setShowAddUserModal(true)}
          >
            Add User
          </button>
        </div>
      </div>
      
      {loading ? (
        <div className="loading">Loading users...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : filteredUsers.length === 0 ? (
        <div className="no-users">No users found.</div>
      ) : (
        <div className="users-table-container">
          <table className="users-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredUsers.map(user => (
                <tr key={user.user_id}>
                  <td>{user.user_id}</td>
                  <td>{user.firstname} {user.lastname}</td>
                  <td>{user.username}</td>
                  <td>{user.email}</td>
                  <td>{user.department || 'N/A'}</td>
                  <td>
                    <span className={getRoleBadgeClass(user.role)}>
                      {user.role.toUpperCase()}
                    </span>
                  </td>
                  <td>
                    <div className="action-buttons">
                      {user.role !== 'admin' && (
                        <button 
                          className="delete-btn"
                          onClick={() => handleDeleteUser(user.user_id)}
                        >
                          Delete
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      
      {/* Add User Modal */}
      {showAddUserModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Add New User</h3>
            <form onSubmit={handleAddUser}>
              <div className="form-group">
                <label>First Name</label>
                <input 
                  type="text" 
                  name="firstname" 
                  value={newUser.firstname} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Last Name</label>
                <input 
                  type="text" 
                  name="lastname" 
                  value={newUser.lastname} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Email</label>
                <input 
                  type="email" 
                  name="email" 
                  value={newUser.email} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Username</label>
                <input 
                  type="text" 
                  name="username" 
                  value={newUser.username} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Password</label>
                <input 
                  type="password" 
                  name="password" 
                  value={newUser.password} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Department</label>
                <input 
                  type="text" 
                  name="department" 
                  value={newUser.department} 
                  onChange={handleNewUserChange} 
                  required 
                />
              </div>
              <div className="form-group">
                <label>Role</label>
                <select 
                  name="role" 
                  value={newUser.role} 
                  onChange={handleNewUserChange} 
                  required
                >
                  <option value="student">Student</option>
                  <option value="faculty">Faculty</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
              <div className="modal-buttons">
                <button type="submit" className="submit-btn">Add User</button>
                <button 
                  type="button" 
                  className="cancel-btn"
                  onClick={() => setShowAddUserModal(false)}
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

export default AdminUsers;