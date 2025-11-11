# AI Sensei Role-Based Access Control (RBAC) Implementation

## Overview
This document describes the implementation of role-based access control for AI Sensei, ensuring that the AI assistant respects existing business logic and user permissions in the college management system.

## Architecture

### Components

#### 1. MCPPermissionService (`app/Services/Communication/Chat/MCP/MCPPermissionService.php`)
- **Purpose**: Centralized permission checking for all MCP (Model Context Protocol) operations
- **Key Methods**:
  - `canAccessMCP()`: Checks if user can access MCP functions at all
  - `canCreateQuestionSets()`: Validates question set creation permissions  
  - `canAddQuestions()`: Validates question addition permissions
  - `canCreateExams()`: Validates exam creation permissions
  - `canManageExams()`: Validates exam management permissions
  - `canViewExams()`: Validates exam viewing permissions
  - `getUserContext()`: Provides user role and permission context
  - `getPermissionDenialMessage()`: Returns user-friendly permission denial messages

#### 2. MCPIntegrationService (`app/Services/Communication/Chat/MCP/MCPIntegrationService.php`)
- **Purpose**: MCP function execution with integrated permission validation
- **Key Features**:
  - Permission validation before any MCP function execution
  - Detailed audit logging of permission checks
  - User context generation for AI assistant instructions
- **Key Methods**:
  - `processFunctionCall()`: Enhanced with permission checks
  - `getUserContextForAssistant()`: Generates user context for AI instructions

#### 3. AISenseiChat (`app/Livewire/Communication/AISenseiChat.php`)
- **Purpose**: Real-time AI chat interface with role awareness
- **Key Updates**:
  - User context integration in `triggerAIResponse()` method
  - User context integration in `sendMessage()` method
  - Both AI run creation calls now include user permission context

## Permission System Integration

### Spatie Laravel Permission Integration
The system leverages the existing Spatie Laravel Permission package with:

#### Roles (15 defined roles):
- Student
- Lecturer  
- Academic Officer
- Administrator
- System
- Super Admin
- Staff
- Parent
- Guest
- Course Coordinator
- Department Head
- IT Support
- Finance Officer
- Registrar
- Dean

#### Exam-Related Permissions:
- create exams
- edit exams  
- view exams
- delete exams
- manage curriculum
- view curriculum
- manage question banks
- view question banks
- And many more...

## User Context Flow

### 1. User Interaction
When a user interacts with AI Sensei:

1. User sends message or uploads files
2. `AISenseiChat` component triggers AI response
3. `getUserContextForAssistant()` is called to generate permission context
4. AI Assistant receives user context in run instructions

### 2. Permission Context Format
The user context includes:
```
User Context:
- Role: [user_role]
- User ID: [user_id]
- Permissions: [comma_separated_permissions]
- MCP Access Level: [access_description]
- Available Actions: [available_actions_list]

Important: Respect these permissions when processing MCP function calls. If the user requests actions they don't have permission for, explain the limitation politely and suggest alternatives within their permission scope.
```

### 3. MCP Function Execution
When AI Assistant calls MCP functions:

1. `MCPIntegrationService.processFunctionCall()` receives the function call
2. `MCPPermissionService` validates user permissions for the specific operation
3. If permitted: Function executes normally
4. If denied: Returns detailed permission denial message with role-specific explanation
5. All permission checks are logged for audit purposes

## Security Features

### 1. Comprehensive Permission Validation
- Every MCP function call is validated against user permissions
- No bypass mechanisms - all operations go through permission checks
- Role-specific permission denial messages

### 2. Audit Logging
- All permission checks are logged with:
  - User ID and role
  - Requested action
  - Permission result (granted/denied)
  - Timestamp
  - Additional context

### 3. Graceful Permission Denials
- User-friendly error messages explain why actions are not permitted
- Role-specific guidance on what actions are available
- Suggestions for alternative approaches within user's permission scope

## Example Usage Scenarios

### Student User
- **Can**: View their own exam results, access study materials
- **Cannot**: Create exams, manage question banks, view other students' data
- **AI Response**: "I understand you'd like to create an exam, but as a Student, you don't have permission to create exams. You can view your exam schedules and results. Would you like me to help with that instead?"

### Lecturer User  
- **Can**: Create exams, add questions, view curriculum for their courses
- **Cannot**: Manage system-wide settings, access all student data
- **AI Response**: AI can help create exams and questions within their course scope

### Administrator User
- **Can**: Full access to exam management, student data, system configuration
- **Cannot**: Nothing restricted at this level
- **AI Response**: AI provides full assistance with all available MCP functions

## Implementation Benefits

### 1. Security
- Prevents unauthorized actions through AI interface
- Maintains existing business logic and permission boundaries
- Comprehensive audit trail for compliance

### 2. User Experience  
- Clear explanations when permissions are insufficient
- Role-appropriate suggestions and alternatives
- Consistent permission enforcement across all interfaces

### 3. Maintainability
- Centralized permission logic in MCPPermissionService
- Leverages existing Spatie permission system
- Easy to extend with new MCP functions or permission rules

### 4. Compliance
- Full audit logging of all permission checks
- Transparent permission enforcement
- Role-based access control aligned with business requirements

## Testing Scenarios

To verify RBAC implementation:

1. **Student Role Test**: Attempt exam creation - should be denied with appropriate message
2. **Lecturer Role Test**: Create exam within course scope - should succeed
3. **Administrator Role Test**: All MCP functions - should have full access
4. **Permission Boundary Test**: Test each role against actions just outside their scope
5. **Audit Log Test**: Verify all permission checks are properly logged

## Maintenance

### Adding New MCP Functions
1. Add permission check method to `MCPPermissionService`
2. Update `processFunctionCall()` in `MCPIntegrationService`
3. Define required permissions in database
4. Test with different user roles

### Modifying Permission Rules
1. Update permission definitions in database
2. Modify permission check logic in `MCPPermissionService` if needed
3. Test across all affected user roles
4. Update documentation

This RBAC implementation ensures AI Sensei operates within the established security and permission framework of the college management system, providing a secure and user-appropriate AI experience.