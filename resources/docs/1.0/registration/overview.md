# Course Registration Module

---

- [Introduction](#introduction)
- [Features](#features)
- [Registration Process](#registration-process)
- [Approval Workflow](#approval-workflow)
- [Course Load Rules](#course-load-rules)
- [Prerequisites Handling](#prerequisites)
- [Configuration](#configuration)

<a name="introduction"></a>
## Introduction

The Course Registration module enables students to register for courses each semester through a streamlined, automated process. It provides an intuitive interface for students to select courses based on their program requirements, while enforcing academic rules such as prerequisites, credit limits, and scheduling constraints.

This module integrates with the Finance module to ensure students meet financial requirements before registration and with the Academic module to ensure proper course selection according to the student's program of study.

<a name="features"></a>
## Features

### For Students

- Self-service course selection and registration
- Real-time course availability information
- Automatic enforcement of prerequisites
- Credit hour limits and warnings
- Class schedule visualization with conflict detection
- Registration status tracking
- Digital submission of registration applications
- Add/drop functionality within allowed periods

### For Faculty Advisors

- Student registration approval interface
- Batch approval capabilities
- Registration statistics for advisees
- Comment and feedback functionality
- Electronic signature for approvals

### For Academic Administration

- Registration period management
- Course capacity management
- Wait list functionality
- Section creation and management
- Registration reports and statistics
- Override capabilities for exceptional cases
- Batch operations for registration processing

<a name="registration-process"></a>
## Registration Process

The student registration process follows these steps:

### 1. Pre-Registration Phase

- **Finance Clearance**: System verifies student has met financial requirements
- **Academic Standing Check**: System confirms student is in good academic standing
- **Course Offering Publication**: Available courses for the semester are published

### 2. Course Selection

- Students log in during the designated registration period
- They view available courses filtered by their program requirements
- The system shows real-time information about course availability, schedule, and capacity
- Students select desired courses and add them to their registration cart

### 3. Validation Checks

- **Prerequisites**: System checks if student has completed required prerequisites
- **Credit Limits**: System enforces minimum and maximum credit hour rules
- **Schedule Conflicts**: System detects and prevents time conflicts
- **Repeat Course Rules**: System applies rules for course repetition
- **Class Capacity**: System checks seat availability

### 4. Submission

- Student reviews registration selections
- System performs final validation of the complete registration package
- Student submits registration for approval
- System generates a registration application number

### 5. Post-Registration

- Registration is queued for advisor approval
- Student receives confirmation of submission via notification
- Student can track registration status through the dashboard
- Add/drop functionality is available during the designated period

<a name="approval-workflow"></a>
## Approval Workflow

Registration applications go through an approval process:

### Faculty Advisor Approval

- Advisor receives notification of pending registration approvals
- Advisor reviews course selections for:
  - Alignment with program requirements
  - Appropriate course load
  - Any special circumstances
- Advisor can:
  - Approve the registration
  - Reject with comments
  - Request modifications
  - Add specific courses

### Department Approval (Optional)

- For certain programs or special cases, department-level approval may be required
- Department officers can review registration details
- They can enforce department-specific policies

### Final Processing

- Approved registrations are finalized in the system
- Course rosters are updated
- Students receive confirmation notifications
- Registration appears in the student's academic record

<a name="course-load-rules"></a>
## Course Load Rules

The system enforces various rules regarding course load:

### Standard Load

- Undergraduate: 12-18 credit hours per semester
- Graduate: 9-12 credit hours per semester

### Special Cases

- **Dean's List Students**: May register for up to 21 credit hours with automatic approval
- **Probation Students**: Limited to 12 credit hours maximum
- **Graduating Students**: May request exceptions for their final semester
- **Part-time Students**: Different minimums apply based on status

### Override Procedures

In special circumstances, load requirements can be overridden:
- Student must request an override through the system
- Academic advisor must approve with justification
- Dean's office provides final approval for exceptional cases

<a name="prerequisites"></a>
## Prerequisites Handling

The system manages course prerequisites in several ways:

### Types of Prerequisites

- **Course Prerequisites**: Specific courses that must be completed
- **Grade Prerequisites**: Minimum grades required in specific courses
- **Credit Hour Prerequisites**: Minimum total credit hours completed
- **Level Prerequisites**: Restrictions based on student academic level
- **Program Prerequisites**: Restrictions based on student's program of study

### Prerequisite Enforcement

- Automatic validation during course selection
- Clear error messages explaining unmet prerequisites
- Suggested alternative courses when prerequisites aren't met

### Prerequisite Overrides

For special cases:
- Instructor can grant prerequisite override
- Department chair can authorize exceptions
- Override requests require justification and approval workflow

<a name="configuration"></a>
## Configuration

The Registration module provides configuration options for:

### Timing Parameters

- **Registration Start/End Dates**: Set by semester and student category
- **Add/Drop Period**: Configurable duration after classes start
- **Late Registration**: Optional period with potential fees

### Registration Rules

- Credit hour minimums and maximums
- Prerequisite enforcement levels
- Wait list functionality and limits
- Registration priority (by class standing, GPA, etc.)

### Approval Requirements

- Which registrations require advisor approval
- Which scenarios require department approval
- Auto-approval conditions for certain student categories

### Notification Settings

- Email/SMS notifications for registration status changes
- Reminder frequency for pending approvals
- Notification templates customization