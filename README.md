# ⚠️🚧 Under Active Development

This project is currently being rebuilt as **Lamatics 2.0**. During this transition, some hosted services or features may be unavailable or behave unexpectedly.

If you experience any issues, please contact me at **[omar.semgey@gmail.com](mailto:omar.semgey@gmail.com)**.

# Lamatics – E-Quiz Platform

[Lamatics](https://lamatics.onrender.com/) is a full-stack e-quiz platform designed to connect teachers with their students in a simple and efficient way. It enables teachers to create and manage quizzes while allowing students to track their academic progress over time.

Lamatics focuses on ease of use, performance, and real-world classroom needs, making digital assessments more accessible and organized.

## 🚀 Features

- Teacher and student role separation

- Quiz creation and management

- Automatic grading and result tracking

- Student performance and progress tracking

- Responsive design (desktop & mobile friendly)

## 🧩 Problem It Solves

Traditional quizzes are often time-consuming to manage and difficult to track long-term performance.
Lamatics however solves this by:

- Giving teachers a centralized platform to assess students efficiently

- Allowing students to track their academic progress in one place

- Reducing manual grading and administrative overhead

- Providing a modern, responsive alternative to paper-based or fragmented quiz systems

## 🛠 Tech Stack

### Frontend

- React

- Axios 

### Backend

- Laravel (RESTful API)

- PostgreSQL (relational database)

- Redis (caching and performance optimization)

## ⚠️ Known Issues / Limitations

* Users who submit quizzes to an older version may experience inconsistencies if the quiz has been updated.
* Uploaded images are not currently stored in cloud storage (e.g., S3) and may be lost after a redeployment.
* Performance may be limited due to the use of free-tier hosting for the frontend, backend, database, and cache.
* The initial request to the backend may be slower because free-tier hosting providers suspend inactive services, resulting in a cold start.
* During the ongoing rebuild of **Lamatics 2.0**, hosted services may occasionally be unavailable. If the demo does not load beyond the initial page, a backend or database service may have reached its free-tier limits or become temporarily unavailable.

## 🔐 Demo Teacher Account

For evaluation and demonstration purposes, a public **teacher account** is provided so reviewers can access the teacher dashboard and explore the platform’s features:

- **Email:** lamatics-test@gmail.com  
- **Password:** lamaticstest  

This account is intended for recruiters and reviewers to view quiz management, student results, and dashboard functionality.

> **Note:** If the live demo is unavailable due to free-tier hosting limits or an expired service, feel free to contact me at **[omar.semgey@gmail.com](mailto:omar.semgey@gmail.com)**. I can redeploy or restore the demo for evaluation.
