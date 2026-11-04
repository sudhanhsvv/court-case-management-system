flowchart TD
A([Start]) --> B["Open CCMS"]
B --> C["Enter Email and Password"]
C --> D{"Valid Credentials?"}

    D -->|"No"| E["Show Error Message"]
    E --> C

    D -->|"Yes"| F["Create Secure Session"]
    F --> G["Open Dashboard"]
    G --> H{"Select Operation"}

    H -->|"Clients"| I["Client Management"]
    H -->|"Cases"| J["Case Management"]
    H -->|"Hearings"| K["Hearing Management"]
    H -->|"Documents"| L["Document Management"]
    H -->|"Reports"| M["View Reports"]

    I --> N["Validate and Save Changes"]
    J --> N
    K --> N
    L --> N
    M --> O["Display Results"]

    N --> P["Update MySQL Database"]
    P --> O
    O --> Q{"Continue Working?"}

    Q -->|"Yes"| G
    Q -->|"No"| R["Logout and Destroy Session"]
    R --> S([End])
