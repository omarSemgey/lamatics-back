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

### Backend

- Laravel (RESTful API)

- PostgreSQL (relational database)

- Redis (caching and performance optimization)

## ⚠️ Known Issues / Limitations

- Users who submit quizzes to an older version may experience inconsistencies when the quiz is updated.
- Uploaded images aren't stored in the cloud or laravel's s3 and may be lost on redeployment.
- Performance may be limited due to free-tier hosting constraints (front-end, back-end, database, and cache are all on free plans).
- Initial backend requests may experience noticeable latency due to free-tier hosting cold starts, where services are temporarily suspended and must be reactivated.

## 🔐 Demo Teacher Account

For evaluation and demonstration purposes, a public **teacher account** is provided so reviewers can access the teacher dashboard and explore the platform’s features:

- **Email:** lamatics-test@gmail.com  
- **Password:** lamaticstest  

This account is intended for recruiters and reviewers to view quiz management, student results, and dashboard functionality.
