
require('dotenv').config();
const express = require('express');
const multer = require('multer');
const fetch = require('node-fetch');
const fs = require('fs');
const path = require('path');
const app = express();
const upload = multer({ dest: 'uploads/' });

const WEBDAV_URL = process.env.WEBDAV_URL;
const USERNAME = process.env.WEBDAV_USER;
const PASSWORD = process.env.WEBDAV_PASS;

function getAuthHeader() {
  const token = Buffer.from(`${USERNAME}:${PASSWORD}`).toString('base64');
  return `Basic ${token}`;
}

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use((req, res, next) => {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  next();
});

app.post('/upload', upload.single('media'), async (req, res) => {
  const file = req.file;
  const fileName = `${Date.now()}_${file.originalname}`;
  const filePath = path.resolve(file.path);

  try {
    const buffer = fs.readFileSync(filePath);
    const uploadRes = await fetch(`${WEBDAV_URL}${fileName}`, {
      method: 'PUT',
      headers: {
        'Authorization': getAuthHeader(),
        'Content-Type': file.mimetype
      },
      body: buffer
    });

    fs.unlinkSync(filePath);

    if (uploadRes.ok) {
      res.json({
        success: true,
        fileUrl: `${WEBDAV_URL}${fileName}`,
        name: fileName
      });
    } else {
      res.status(500).json({ error: 'Errore durante l\'upload.' });
    }
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/save-classifica', async (req, res) => {
  const classifica = JSON.stringify(req.body, null, 2);

  try {
    const saveRes = await fetch(`${WEBDAV_URL}classifica.json`, {
      method: 'PUT',
      headers: {
        'Authorization': getAuthHeader(),
        'Content-Type': 'application/json'
      },
      body: classifica
    });

    if (saveRes.ok) {
      res.json({ success: true });
    } else {
      res.status(500).json({ error: 'Errore nel salvataggio della classifica' });
    }
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/get-classifica', async (req, res) => {
  try {
    const fetchRes = await fetch(`${WEBDAV_URL}classifica.json`, {
      headers: {
        'Authorization': getAuthHeader()
      }
    });

    if (fetchRes.ok) {
      const data = await fetchRes.json();
      res.json(data);
    } else {
      res.status(404).json({ error: 'Classifica non trovata' });
    }
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Backend in esecuzione su http://localhost:${PORT}`);
});
