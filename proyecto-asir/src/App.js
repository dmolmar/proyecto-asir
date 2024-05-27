import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import axios from 'axios';
import './styles/App.css';
import Home from './components/Home';
import About from './components/About';
import Contact from './components/Contact';
import Login from './components/Login';
import Register from './components/Register';
import Navbar from './components/Navbar';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [username, setUsername] = useState('');
  const [avatar, setAvatar] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      setIsAuthenticated(true);
      // Obtén los datos del perfil del usuario
      axios.get('http://localhost:5000/api/profile', {
        headers: { Authorization: `Bearer ${token}` }
      })
      .then(response => {
        setUsername(response.data.name);
        setAvatar(response.data.avatar);
      })
      .catch(error => {
        console.error('Failed to fetch profile:', error);
      });
    }
  }, []);

  const handleRegister = () => {
    setIsAuthenticated(true);
  };

  const handleLogin = () => {
    setIsAuthenticated(true);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    setIsAuthenticated(false);
    setUsername('');
    setAvatar(null);
  };

  return (
    <Router>
      <div className="App">
        <header className="App-header">
          <Navbar isAuthenticated={isAuthenticated} onLogout={handleLogout} username={username} avatar={avatar} />
        </header>
        <main className="App-content">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/about" element={<About />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/login" element={<Login onLogin={handleLogin} />} />
            <Route path="/register" element={<Register onRegister={handleRegister} />} />
          </Routes>
        </main>
        <footer className="App-footer">
          <p>Pie de Página bonito</p>
        </footer>
      </div>
    </Router>
  );
}

export default App;