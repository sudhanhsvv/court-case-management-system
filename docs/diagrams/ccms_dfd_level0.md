flowchart LR
A["Administrator"] -->|"Login details, user management, case and client data"| S(("Court Case Management System"))
S -->|"Dashboard, reports, confirmations"| A

    B["Staff"] -->|"Login details, client records, case updates, hearing details"| S
    S -->|"Case information, hearing schedules, document details"| B

    S <--> D[("CCMS MySQL Database")]
