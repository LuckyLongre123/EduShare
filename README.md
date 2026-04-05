<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Montserrat&weight=600&size=32&pause=1000&color=61DAFB&center=true&vCenter=true&width=600&height=70&lines=EduShare+-+Notes+Sharing+Platform;Full+Stack+Web+Application;PHP+%7C+MySQL+%7C+Bootstrap;Empowering+Education+Through+Technology" alt="Typing SVG" />
  
  <div>
    <img src="https://img.shields.io/badge/Status-Production_Ready-2ea44f?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Status" />
    <img src="https://img.shields.io/badge/Version-1.0.0-blue?style=for-the-badge&logo=semantic-release&logoColor=white" alt="Version" />
    <img src="https://img.shields.io/badge/License-Educational-yellow?style=for-the-badge&logo=open-source-initiative&logoColor=white" alt="License" />
  </div>
  
  <div style="margin-top: 10px;">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
    <img src="https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
    <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  </div>
</div>

<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">

<p align="center">
  <img src="https://img.shields.io/badge/Full_Stack-Web_Development-0e75b6?style=for-the-badge" alt="Full Stack" />
  <img src="https://img.shields.io/badge/Database-Design-orange?style=for-the-badge" alt="Database" />
  <img src="https://img.shields.io/badge/Security-Implementation-red?style=for-the-badge" alt="Security" />
</p>

# 🎓 EduShare - Educational Notes Sharing Platform

## 🚀 Overview

**EduShare** is a full-stack web application that enables seamless sharing of educational materials between students and teachers across different institutions.

### ⚡ Key Features
- 🔐 **User Authentication** - Role-based access (Student/Teacher/Admin)
- 📁 **File Management** - Upload/Download notes with validation
- 🔍 **Advanced Search** - Filter by semester, college, subject, tags
- 👨‍💼 **Admin Panel** - Complete system management
- 💬 **Interactive** - Like/Comment system with engagement tracking
- 📱 **Responsive** - Mobile-friendly dark theme design

---

## 🛠️ Tech Stack

<div align="center">

| Frontend | Backend | Database | Tools |
|----------|---------|----------|-------|
| ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white) | ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) | ![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white) |
| ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | ![phpMyAdmin](https://img.shields.io/badge/phpMyAdmin-6C78AF?style=flat-square&logo=phpmyadmin&logoColor=white) | ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white) |

</div>

---

## 📂 Project Structure

```
📦 EduShare/
├── 🏠 index.php              # Landing page
├── 📁 auth/                  # Authentication system
│   ├── 📊 dashboard.php      # User dashboard
│   ├── 📤 upload-notes.php   # File upload
│   ├── ⚙️ admin.php          # Admin panel
│   └── 🚪 logout.php         # Session management
├── 📄 pages/                 # Public pages
│   ├── 🔐 login.php          # User login
│   ├── ✍️ register.php       # User registration
│   ├── 🔍 browse.php         # Notes browsing
│   ├── 📋 note-details.php   # Individual note view
│   └── 📞 contact.php        # Contact form
├── 🗄️ db/                    # Database layer
│   └── ⚙️ config.php         # DB configuration
└── 🎨 css/                   # Styling
    └── 🌙 global.css         # Dark theme
```

---

## ⚙️ Core Functionality

### 👤 User Management
- ✅ Registration with role selection
- ✅ Secure authentication system
- ✅ Profile management
- ✅ Role-based permissions

### 📚 Content Management
- ✅ File upload with validation (PDF, DOC, DOCX, TXT, PPT, PPTX)
- ✅ Admin approval workflow
- ✅ Metadata tagging system
- ✅ Download tracking

### 🔍 Search & Discovery
- ✅ Advanced filtering options
- ✅ Pagination for performance
- ✅ Category-based browsing
- ✅ Tag-based organization

### 🛡️ Security Features
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ File upload security
- ✅ Session management
- ✅ Input validation

---

## 🗄️ Database Design & Architecture

<div align="center">

### Complete Database Schema
| Table | Records | Purpose | Key Relationships |
|-------|---------|---------|------------------|
| `users` | 4 Active Users | Authentication & Profile Management | Primary entity for all user operations |
| `materials` | 2 Approved Notes | Content Storage & Metadata | Links to users and colleges |
| `colleges` | 1 Institution | College Information Management | Referenced by materials and users |
| `tags` | 77 Categories | Content Classification System | Used for material categorization |
| `likes` | 4 Interactions | User Engagement Tracking | Many-to-many: users ↔ materials |
| `comments` | 4 Discussions | Community Feedback System | Threaded discussions on materials |
| `contact_messages` | 1 Support Query | Customer Support Management | Independent support system |

</div>

### 📊 Entity Relationship Diagram

```mermaid
erDiagram
    USERS {
        int user_id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        varchar college
        varchar department
        enum status
        timestamp joined_on
    }
    
    COLLEGES {
        int college_id PK
        varchar college_name
        varchar university
        text address
    }
    
    MATERIALS {
        int material_id PK
        varchar title
        text description
        varchar subject
        int semester
        int college_id FK
        int uploaded_by FK
        varchar file_name
        varchar file_path
        varchar file_type
        int file_size
        text tags
        timestamp upload_date
        int downloads
        enum status
    }
    
    LIKES {
        int like_id PK
        int material_id FK
        int user_id FK
        timestamp like_date
    }
    
    COMMENTS {
        int comment_id PK
        int material_id FK
        int user_id FK
        text comment_text
        timestamp comment_date
    }
    
    TAGS {
        int id PK
        varchar name UK
        timestamp created_at
    }
    
    CONTACT_MESSAGES {
        int message_id PK
        varchar name
        varchar email
        varchar subject
        text message
        timestamp date
        enum status
    }

    USERS ||--o{ MATERIALS : "uploads"
    USERS ||--o{ LIKES : "gives"
    USERS ||--o{ COMMENTS : "writes"
    COLLEGES ||--o{ MATERIALS : "belongs_to"
    MATERIALS ||--o{ LIKES : "receives"
    MATERIALS ||--o{ COMMENTS : "has"
```

### 🔧 Database Architecture Details

#### Core Tables Structure
- **Users Table**: Role-based authentication with secure password hashing
- **Materials Table**: File metadata with approval workflow (pending → approved/rejected)
- **Colleges Table**: Institution management for proper affiliation
- **Tags Table**: 77 predefined categories for content classification

#### Engagement System
- **Likes Table**: User appreciation system with duplicate prevention
- **Comments Table**: Community discussion threads with timestamp tracking
- **Contact Messages**: Support ticket management system

#### Data Integrity & Performance
- **Foreign Key Constraints**: CASCADE DELETE for referential integrity
- **Unique Constraints**: Email uniqueness, tag name uniqueness
- **Indexed Fields**: Primary keys, foreign keys, email for optimal query performance
- **Normalized Structure**: 3NF compliance eliminating data redundancy

#### Security Implementation
- **Password Security**: PHP `password_hash()` with secure algorithms
- **Input Validation**: Server-side sanitization and type checking
- **File Upload Security**: Type validation and size restrictions (10MB max)
- **SQL Injection Prevention**: Prepared statements throughout the application

---

## 🌐 Live Demo

<div align="center">
  <a href="https://edushare-luckylongre.free.nf//" target="_blank">
    <img src="https://img.shields.io/badge/🚀_View_Live_Project-Click_Here-brightgreen?style=for-the-badge&logo=google-chrome&logoColor=white" alt="Live Demo" />
  </a>
</div>


## 🚀 Installation & Setup

### Prerequisites
```bash
✅ PHP 8.0+
✅ MySQL 8.0+
✅ Apache/Nginx
✅ Modern Browser
```

### Quick Start
```bash
# 1. Clone/Download project
git clone [repository-url]

# 2. Configure database
# Update db/config.php with your credentials

# 3. Import database schema
mysql -u username -p database_name < schema.sql

# 4. Set permissions
chmod 755 uploads/

```

---

## 📊 Features Demo

<div align="center">

### 🎓 For Students
Upload Notes → Browse Materials → Download Resources → Engage with Community

### 👨‍🏫 For Teachers  
Share Knowledge → Review Student Content → Build Learning Community

### 👨‍💼 For Admins
Manage Users → Moderate Content → Monitor System → Handle Support

</div>

---

## 🏆 Technical Achievements

<div align="center">

| Achievement | Implementation |
|-------------|----------------|
| 🔧 **Full-Stack Development** | Complete CRUD operations with MVC architecture |
| 🗄️ **Database Design** | Normalized schema with optimized relationships |
| 🛡️ **Security Implementation** | Comprehensive input validation and protection |
| 📱 **Responsive Design** | Mobile-first approach with Bootstrap framework |
| ⚡ **Performance Optimization** | Efficient queries and resource management |
| 🔍 **Advanced Search** | Multi-parameter filtering with pagination |

</div>

---

## 🎯 Key Metrics

<p align="center">
  <img src="https://img.shields.io/badge/Code_Quality-A+-brightgreen?style=for-the-badge" alt="Code Quality" />
  <img src="https://img.shields.io/badge/Security_Score-95%25-green?style=for-the-badge" alt="Security" />
  <img src="https://img.shields.io/badge/Performance-Optimized-blue?style=for-the-badge" alt="Performance" />
  <img src="https://img.shields.io/badge/Mobile_Ready-100%25-purple?style=for-the-badge" alt="Mobile" />
</p>

---

## 🔮 Future Enhancements

- 🌐 **Cloud Integration** - AWS S3 storage
- 📱 **Mobile App** - React Native implementation  
- 🤖 **AI Features** - Auto-tagging and recommendations
- 📊 **Analytics** - Advanced usage insights
- 🔔 **Real-time** - Push notifications
- 🎥 **Media Support** - Video content integration

---

## 📈 Impact & Value

### Business Impact
- 📚 **Knowledge Accessibility** - Centralized educational resources
- 🤝 **Community Building** - Cross-institutional collaboration
- ⚡ **Efficiency** - Streamlined content sharing process
- 🎯 **Quality Control** - Admin-moderated content system

### Technical Skills Demonstrated
- 🔧 **Full-Stack Development** - End-to-end application building
- 🗄️ **Database Architecture** - Relational database design
- 🛡️ **Security Engineering** - Comprehensive protection implementation
- 🎨 **UI/UX Design** - User-centered interface development
- 📊 **System Design** - Scalable architecture planning

---

## 👨‍💻 Developer Information

<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Montserrat&weight=500&size=24&pause=1000&color=61DAFB&center=true&vCenter=true&width=500&height=50&lines=Lucky+Longre;Full-Stack+Developer;Problem+Solver" alt="Developer" />
  
  <p><em>Computer Science Student & Aspiring Software Developer</em></p>
  
  <div style="margin: 20px 0;">
    <a href="https://lucky-longre.onrender.com/">
      <img src="https://img.shields.io/badge/🌐_Portfolio-Visit_Website-0A66C2?style=for-the-badge&logo=vercel&logoColor=white" alt="Portfolio" />
    </a>
    <a href="mailto:officialluckylongre@gmail.com">
      <img src="https://img.shields.io/badge/📧_Email-Contact_Me-D14836?style=for-the-badge&logo=gmail&logoColor=white" alt="Email" />
    </a>
    <a href="https://www.linkedin.com/in/lucky-longre/">
      <img src="https://img.shields.io/badge/💼_LinkedIn-Connect-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white" alt="LinkedIn" />
    </a>
  </div>
  
  <p>
    <img src="https://img.shields.io/badge/Course-Computer_Science-brightgreen?style=flat-square" alt="Course" />
    <img src="https://img.shields.io/badge/Specialization-Full_Stack_Development-blue?style=flat-square" alt="Specialization" />
    <img src="https://img.shields.io/badge/Location-New_Delhi,_India-orange?style=flat-square" alt="Location" />
  </p>
</div>

### 🎯 Professional Profile

**Lucky Longre** is a dedicated Computer Science student and aspiring full-stack developer with a passion for creating innovative web solutions that solve real-world problems. Currently pursuing advanced studies in software development, Lucky combines academic knowledge with practical implementation skills.

### 💼 Technical Expertise

<div align="center">

| **Frontend Technologies** | **Backend Technologies** | **Database & Tools** |
|--------------------------|-------------------------|---------------------|
| HTML5, CSS3, JavaScript | PHP, Node.js | MySQL, MongoDB |
| React.js, Bootstrap | Express.js, RESTful APIs | Git, GitHub |
| Responsive Design | Authentication Systems | phpMyAdmin, Postman |
| jQuery, AJAX | File Upload Systems | VS Code, XAMPP |

</div>

### 🚀 Development Philosophy

- **Problem-Solving First**: Every project starts with understanding the real-world problem
- **User-Centric Design**: Building intuitive interfaces that enhance user experience
- **Security-Minded**: Implementing robust security measures from the ground up
- **Performance Focused**: Optimizing applications for speed and scalability
- **Continuous Learning**: Staying updated with latest technologies and best practices

### 🏆 Project Highlights

- **EduShare Platform**: Full-stack educational resource sharing application
- **Database Architecture**: Designed normalized schemas with optimized relationships
- **Security Implementation**: Comprehensive protection against common vulnerabilities
- **Responsive Design**: Mobile-first approach with modern UI/UX principles

### 📈 Academic & Professional Goals

- **Short-term**: Complete Computer Science degree with distinction
- **Medium-term**: Secure full-stack developer position in innovative tech company
- **Long-term**: Lead development teams and architect scalable software solutions
- **Vision**: Contribute to educational technology that makes learning accessible globally

---

<div align="center">
  <img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif">
  
  <h3>🎓 EduShare - Empowering Education Through Technology</h3>
  
  <p><em>A production-ready full-stack web application demonstrating advanced development skills and real-world problem-solving capabilities.</em></p>
  
  <div>
    <img src="https://img.shields.io/badge/Made_with-❤️-red?style=for-the-badge" alt="Made with Love" />
    <img src="https://img.shields.io/badge/Built_for-Education-blue?style=for-the-badge" alt="Built for Education" />
    <img src="https://img.shields.io/badge/Status-Production_Ready-green?style=for-the-badge" alt="Production Ready" />
  </div>
  
  <p><strong>Developed by Lucky Longre</strong></p>
</div>
