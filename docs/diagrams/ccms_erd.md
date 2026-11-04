erDiagram
USERS ||--o{ DOCUMENTS : uploads
CLIENTS ||--o{ CASES : owns
CASES ||--o{ HEARINGS : has
CASES ||--o{ DOCUMENTS : contains

    USERS {
        int user_id PK
        varchar full_name
        varchar email UK
        varchar password
        enum role
        enum status
    }

    CLIENTS {
        int client_id PK
        varchar full_name
        varchar phone
        varchar email
        text address
        enum status
    }

    CASES {
        int case_id PK
        varchar case_number UK
        varchar title
        varchar case_type
        int client_id FK
        varchar advocate_name
        varchar court_name
        date filing_date
        enum status
        enum priority
    }

    HEARINGS {
        int hearing_id PK
        int case_id FK
        date hearing_date
        time hearing_time
        varchar courtroom
        varchar purpose
        text outcome
    }

    DOCUMENTS {
        int document_id PK
        int case_id FK
        int uploaded_by FK
        varchar document_name
        varchar stored_filename UK
        varchar file_path
        varchar file_type
    }
