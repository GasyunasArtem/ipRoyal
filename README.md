# ipRoyal API

Laravel REST API for user profiling and points management system.

## Overview

This application provides a REST API for collecting user profile data through questionnaires and rewarding users with points that can be converted to USD. The system supports multiple question types including single choice, multiple choice, and date inputs.

## Core Features

- User authentication with Sanctum tokens
- Dynamic profiling questionnaires with multiple question types
- Points-based reward system
- Daily statistics and analytics
- Rate limiting and security controls
- Email notifications for point claims

## Architecture

### Backend Stack
- Laravel 12 REST API
- MySQL 8.0 database
- Docker containerization
- Queue system for background processing
- Sanctum for API authentication

### Question Types
- `single` - Single choice questions (radio buttons)
- `multiple` - Multiple choice questions (checkboxes) 
- `date` - Date input questions

### Security Features
- VPN/Proxy detection and blocking
- Rate limiting on all endpoints
- Daily profile update restrictions
- Comprehensive input validation
- SQL injection protection

## API Endpoints

### Authentication
```
POST /api/auth/register  - User registration
POST /api/auth/login     - User login
POST /api/auth/logout    - User logout
```

### Profile Management
```
GET  /api/profiling-questions  - Get available questions
GET  /api/profile              - Get user profile data
POST /api/profile              - Update user profile
```

### Points & Wallet
```
GET  /api/wallet                - Get wallet information
GET  /api/points/transactions   - Get points transaction history
POST /api/points/claim          - Claim points to wallet
```

### Statistics
```
GET  /api/stats/daily           - Get daily statistics
GET  /api/stats/total           - Get total statistics
GET  /api/stats/{date}          - Get statistics for specific date
POST /api/stats/{date}/refresh  - Recalculate statistics
```

## Data Flow

1. User registers and receives authentication token
2. User retrieves available profiling questions
3. User submits profile answers (limited to once per day)
4. System awards points for profile completion
5. User can claim points to convert to USD
6. System tracks statistics for analytics

## Configuration

### Environment Variables
```
POINTS_USD_RATE=0.01                # Points to USD conversion rate
PROFILE_UPDATE_POINTS=5             # Points earned per profile update
RATE_LIMIT_PROFILE=50               # Daily profile update limit
MAX_PROFILE_ANSWERS=50              # Maximum answers per request
BLOCK_VPN_USERS=true                # Enable VPN blocking
```

### Business Logic
- Profile updates limited to once per day per user
- Points conversion rate: 100 points = $1.00 USD
- Automatic email notifications on point claims
- Daily statistics calculation via scheduled jobs

### Email Configuration
The application uses MailHog for local email testing:
- MailHog web interface: http://localhost:8025
- SMTP server runs on port 1025
- Emails are queued and processed by supervisor-managed workers
- Configuration in `docker-compose.yml` and `config/mail.php`

## Database Schema

### Core Tables
- `users` - User accounts and basic information
- `profiling_questions` - Question definitions and options
- `user_profile_answers` - User responses to questions
- `wallets` - User wallet balances
- `points_transactions` - Points earning and claiming history
- `daily_stats` - Aggregated daily statistics

### Relationships
- One user has one wallet
- One user has many profile answers
- One user has many points transactions
- Questions can have multiple user answers

## Development Setup

### Requirements
- Docker and Docker Compose
- Git

### Installation
```bash
git clone <repository>
cd example-app
docker-compose up -d
```

### Rebuilding After Configuration Changes
If you modify configuration files  you need to rebuild the Docker image:
```bash
docker-compose down
docker-compose build app
docker-compose up -d
```

### Database Migration
```bash
docker exec -it laravel_app php artisan migrate:fresh --seed
```

### Clear Cache After Config Changes
```bash
docker exec -it laravel_app php artisan config:clear
docker exec -it laravel_app php artisan cache:clear
```

### Running Tests
```bash
docker exec -it laravel_app php artisan test
```

## Testing

The application includes comprehensive test coverage:

- Feature tests for all API endpoints
- Validation tests for all question types
- Authentication and authorization tests
- Business logic tests for daily restrictions
- Database integrity tests

Test files located in `tests/Feature/` directory.

## API Usage Examples

### Complete User Flow
1. Register user account
2. Login to receive token
3. Get available questions
4. Submit profile answers
5. Check earned points
6. Claim points to wallet

### Sample Requests

**Registration:**
```json
POST /api/auth/register
{
    "name": "John Doe",
    "email": "john@example.com", 
    "password": "SecurePass123!",
    "country": "US"
}
```

**Profile Update with Mixed Question Types:**
```json
POST /api/profile
Authorization: Bearer {token}
{
    "answers": {
        "1": "Male",
        "2": "1990-01-15",
        "3": ["Technology", "Sports", "Reading"],
        "4": ["JavaScript", "Python"]
    }
}
```

## Performance Considerations

- Database indexes on frequently queried columns
- Query optimization for statistics aggregation
- Rate limiting to prevent abuse
- Background job processing for heavy operations
- Efficient JSON storage for multiselect answers

## Security Measures

- Sanctum token-based authentication
- Request validation and sanitization  
- SQL injection prevention via Eloquent ORM
- Rate limiting on all endpoints
- VPN/Proxy detection and blocking
- Daily operation limits to prevent abuse

## Deployment

The application is containerized and production-ready. Key deployment considerations:

- Configure environment variables for production
- Set up proper database backups
- Configure queue workers for background processing
- Set up monitoring and logging
- Configure proper rate limiting values
- Set up email service for notifications

## API Documentation

Complete API documentation available via Postman collection in `postman/` directory. Import the collection and environment files for interactive testing.

