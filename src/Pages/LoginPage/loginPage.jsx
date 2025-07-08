import React, { useState, useContext, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { signInWithEmailAndPassword } from "firebase/auth";
import { auth } from "../../firebase";
import { AuthContext } from "../../context/AuthContext";
import "./loginPage.css";

function LoginPage() {
  const [showPassword, setShowPassword] = useState(false);
  const [credential, setCredential] = useState("");
  const [password, setPassword] = useState("");
  const [loginError, setLoginError] = useState("");
  const navigate = useNavigate();
  const { login, loading, error, user, isAuthenticated } = useContext(AuthContext);

  // If user is already logged in, redirect to appropriate dashboard
  useEffect(() => {
    console.log('Login page useEffect - isAuthenticated:', isAuthenticated, 'user:', user);
    if (isAuthenticated && user) {
      console.log('User role in useEffect:', user.role);
      if (user.role === "admin") {
        console.log('Redirecting to admin dashboard from useEffect');
        navigate("/admin/dashboard");
      } else if (user.role === "approver") {
        navigate("/approver/dashboard");
      } else if (user.role === "sysadmin") {
        navigate("/sysadmin");
      } else if (user.role === "vpo") {
        navigate("/vpo");
      } else {
        console.log('Redirecting to user dashboard from useEffect');
        navigate("/dashboard");
      }
    }
  }, [isAuthenticated, user, navigate]);

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoginError("");
    
    try {
      let email = credential;
      
      // If credential is not an email, show error
      if (!credential.includes('@')) {
        setLoginError("Please use your email address to login");
        return;
      }
      
      // Sign in with Firebase
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      const user = userCredential.user;
      
      // Check if email is verified (skip for VPO accounts)
      const skipVerificationEmails = ['admin@example.com', 'systemadmin@example.com', 'VPO@example.com', 'vpo@example.com'];
      if (!user.emailVerified && !skipVerificationEmails.includes(user.email)) {
        setLoginError("Please verify your email before logging in.");
        return;
      }
      
      // Update AuthContext with Firebase user data
      const result = await login({ firebaseUser: user });
      
      console.log('Login result:', result);
      console.log('User role:', result.user?.role);
      
      if (result.success) {
        if (result.user.role === "admin") {
          console.log('Redirecting to admin dashboard');
          navigate("/admin/dashboard");
        } else if (result.user.role === "sysadmin") {
          console.log('Redirecting to sysadmin dashboard');
          navigate("/sysadmin");
        } else if (result.user.role === "vpo") {
          console.log('Redirecting to VPO dashboard');
          navigate("/vpo");
        } else {
          console.log('Redirecting to user dashboard');
          navigate("/dashboard");
        }
      } else {
        setLoginError("Login failed.");
      }
    } catch (error) {
      console.error("Login error:", error);
      if (error.code === 'auth/user-not-found' || error.code === 'auth/wrong-password') {
        setLoginError("Invalid credentials");
      } else if (error.code === 'auth/too-many-requests') {
        setLoginError("Too many failed attempts. Please try again later.");
      } else {
        setLoginError(error.message || "Login failed. Please try again.");
      }
    }
  };

  return (
    <div className="login-container">
      <h1>LOGIN</h1>
      
      {(loginError || error) && (
        <div className="error-message">
          {loginError || error}
        </div>
      )}
      
      <form onSubmit={handleSubmit}>
        <label htmlFor="credential">Email:</label>
        <input
          type="text"
          id="credential"
          name="credential"
          value={credential}
          onChange={(e) => setCredential(e.target.value)}
          required
        />

        <label htmlFor="password">Password:</label>
        <input
          type={showPassword ? "text" : "password"}
          id="password"
          name="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />

        <div className="show-password">
          <input
            type="checkbox"
            id="show-password"
            onChange={togglePasswordVisibility}
            checked={showPassword}
          />
          <label htmlFor="show-password">Show password</label>
        </div>

        <button type="submit" disabled={loading}>
          {loading ? "LOGGING IN..." : "LOGIN"}
        </button>
      </form>

      <div className="login-options">
        <p className="register-link">
          Don't have an account? <Link to="/register">Register here</Link>
        </p>
      </div>
    </div>
  );
}

export default LoginPage;