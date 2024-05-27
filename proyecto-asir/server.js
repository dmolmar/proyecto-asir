// server.js
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const mysql = require('mysql2');
const bcrypt = require('bcrypt');

const app = express();
const PORT = 5000;
const SECRET_KEY = 'y~I24!w&9"d30HHS3/;q7,krgSS^.//m';

// Configura la conexión a la base de datos MySQL
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '', // Asegúrate de cambiar esto si tienes una contraseña
  database: 'linkedai'
});

db.connect(err => {
  if (err) {
    console.error('Error connecting to MySQL:', err);
  } else {
    console.log('Connected to MySQL');
  }
});

app.use(bodyParser.json());
app.use(cors());

// Función para generar una cadena aleatoria
function generateRandomString(length) {
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return result;
}

// Ruta para registrar un nuevo usuario
app.post('/api/register', (req, res) => {
  const { email, password } = req.body;

  // Verificar si el usuario ya existe
  db.query('SELECT * FROM users WHERE email = ?', [email], (err, results) => {
    if (err) return res.status(500).send('Server error');
    if (results.length > 0) return res.status(400).send('User already exists');

    // Hashear la contraseña
    const hashedPassword = bcrypt.hashSync(password, 10);

    // Insertar el nuevo usuario en la base de datos
    db.query('INSERT INTO users (email, password_hash) VALUES (?, ?)', [email, hashedPassword], (err, results) => {
      if (err) return res.status(500).send('Server error');

      // Crear un nuevo registro en userprofiles
      const userId = results.insertId;
      const name = generateRandomString(10); // Genera un nombre de usuario aleatorio
      db.query('INSERT INTO userprofiles (user_id, name) VALUES (?, ?)', [userId, name], (err) => {
        if (err) return res.status(500).send('Server error');

        const token = jwt.sign({ id: userId }, SECRET_KEY, { expiresIn: '1h' });
        res.json({ token });
      });
    });
  });
});

// Ruta para iniciar sesión
app.post('/api/login', (req, res) => {
  const { email, password } = req.body;

  // Verificar las credenciales del usuario
  db.query('SELECT * FROM users WHERE email = ?', [email], (err, results) => {
    if (err) return res.status(500).send('Server error');
    if (results.length === 0) return res.status(401).send('Invalid credentials');

    const user = results[0];
    const passwordIsValid = bcrypt.compareSync(password, user.password_hash);
    if (!passwordIsValid) return res.status(401).send('Invalid credentials');

    const token = jwt.sign({ id: user.id }, SECRET_KEY, { expiresIn: '1h' });
    res.json({ token });
  });
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});

// Ruta para obtener el perfil del usuario
app.get('/api/profile', (req, res) => {
  const token = req.headers.authorization.split(' ')[1];
  const { id: userId } = jwt.verify(token, SECRET_KEY);
  console.log(`Server running on port ${PORT}`);

  db.query('SELECT * FROM userprofiles WHERE user_id = ?', [userId], (err, results) => {
    if (err) return res.status(500).send('Server error');
    if (results.length === 0) return res.status(404).send('Profile not found');

    const profile = results[0];
    res.json(profile);
  });
});
