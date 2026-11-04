flowchart TB
A["Administrator / Staff"]

    P1(("1.0 Authentication"))
    P2(("2.0 Client Management"))
    P3(("3.0 Case Management"))
    P4(("4.0 Hearing Management"))
    P5(("5.0 Document Management"))
    P6(("6.0 Reports and Dashboard"))

    D1[("Users")]
    D2[("Clients")]
    D3[("Cases")]
    D4[("Hearings")]
    D5[("Documents")]

    A -->|"Login credentials"| P1
    P1 <-->|"User account data"| D1
    P1 -->|"Login result"| A

    A -->|"Client details / requests"| P2
    P2 <-->|"Client records"| D2
    P2 -->|"Client information"| A

    A -->|"Case details / updates"| P3
    P3 <-->|"Case records"| D3
    P3 -->|"Case information"| A
    P3 -->|"Client reference"| D2

    A -->|"Hearing details"| P4
    P4 <-->|"Hearing records"| D4
    P4 -->|"Case reference"| D3
    P4 -->|"Hearing schedule"| A

    A -->|"Document upload / request"| P5
    P5 <-->|"Document metadata"| D5
    P5 -->|"Case reference"| D3
    P5 -->|"Document result"| A

    A -->|"Report request"| P6
    P6 -->|"Read case data"| D3
    P6 -->|"Read client data"| D2
    P6 -->|"Read hearing data"| D4
    P6 -->|"Dashboard / reports"| A
