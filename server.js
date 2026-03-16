const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

app.post('/send-email', async (req, res) => {
    const { name, email, service, message } = req.body;

    // Create a transporter using your Gmail account
    let transporter = nodemailer.createTransport({
        service: 'gmail',
        auth: {
            user: 'highhopesservices98@gmail.com', // Your Gmail address
            pass: 'jxkt ujgl syru elxc'   // Your generated App Password
        }
    });

    // Email content
    let mailOptions = {
        from: 'highhopes98@gmail.com', // Sender address
        to: 'highhopes98@gmail.com',   // Recipient address (your email)
        subject: `New Contact Form Submission from ${name}`,
        html: `
            <p>You have received a new message from your website contact form.</p>
            <h3>Contact Details:</h3>
            <ul>
                <li><strong>Name:</strong> ${name}</li>
                <li><strong>Email:</strong> ${email}</li>
                <li><strong>Service of Interest:</strong> ${service}</li>
            </ul>
            <h3>Message:</h3>
            <p>${message}</p>
        `
    };

    try {
        await transporter.sendMail(mailOptions);
        console.log('Email sent successfully');
        res.status(200).send('Message sent successfully!');
    } catch (error) {
        console.error('Error sending email:', error);
        res.status(500).send('Error sending message.');
    }
});

app.listen(port, () => {
    console.log(`Server listening at http://localhost:${port}`);
});