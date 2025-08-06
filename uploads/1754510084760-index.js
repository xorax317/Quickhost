const express = require('express');
const cors = require('cors');
const multer = require('multer');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// GitHub repo info
const GITHUB_TOKEN = process.env.GITHUB_TOKEN;
const GITHUB_REPO = 'xorax317/Quickhost';
const GITHUB_FOLDER = 'uploads';

// Middleware
app.use(cors());
app.use(express.json());

// File upload config
const upload = multer({ dest: 'uploads/' });

app.post('/', upload.single('file'), async (req, res) => {
  if (!req.file) return res.status(400).send('❌ No file uploaded.');

  const filePath = req.file.path;
  const fileName = `${Date.now()}-${req.file.originalname}`;
  const fileContent = fs.readFileSync(filePath).toString('base64');

  try {
    const githubApi = `https://api.github.com/repos/${GITHUB_REPO}/contents/${GITHUB_FOLDER}/${fileName}`;

    await axios.put(githubApi, {
      message: `Upload ${fileName}`,
      content: fileContent
    }, {
      headers: {
        Authorization: `token ${GITHUB_TOKEN}`,
        Accept: 'application/vnd.github.v3+json'
      }
    });

    const fileUrl = `https://raw.githubusercontent.com/${GITHUB_REPO}/main/${GITHUB_FOLDER}/${fileName}`;

    res.send(`
      ✅ File uploaded to GitHub!<br>
      📎 <a href="${fileUrl}" target="_blank">${fileUrl}</a>
    `);

    fs.unlinkSync(filePath); // Clean up

  } catch (err) {
    console.error(err.response?.data || err.message || err);
    res.status(500).send('❌ Failed to upload to GitHub');
  }
});

// Test route
app.get('/', (req, res) => {
  res.send('📦 QuickHost backend is running.');
});

app.listen(PORT, () => {
  console.log(`✅ Server running at http://localhost:${PORT}`);
});