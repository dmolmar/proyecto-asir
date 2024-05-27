import React, { useState } from 'react';
import axios from 'axios';

function ProfileEdit() {
  const [name, setName] = useState('');
  const [avatar, setAvatar] = useState(null);
  // ... otros campos del perfil ...

  const handleSubmit = (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('name', name);
    formData.append('avatar', avatar);
    // ... otros campos del perfil ...
    axios.post('http://localhost:5000/api/profile/edit', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then(response => {
      alert('Perfil actualizado con éxito');
    })
    .catch(error => {
      console.error('Failed to update profile:', error);
    });
  };

  return (
    <div className="profile-edit-form">
      <h2>Editar Perfil</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Nombre:</label>
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Avatar:</label>
          <input
            type="file"
            onChange={(e) => setAvatar(e.target.files[0])}
          />
        </div>
        {/* ... otros campos del perfil ... */}
        <button type="submit">Guardar Cambios</button>
      </form>
    </div>
  );
}

export default ProfileEdit;
