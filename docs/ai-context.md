# AI Context - MaBoussole CRM v2

## Current Context (Updated: 2024-12-24)
- **Project Phase**: Development
- **Current Sprint**: 2 - Tests et Optimisation
- **Progress**: 80% (42/47 points)
- **Framework**: Laravel 10.x with Filament 3.x
- **Database**: MySQL with French-first approach

## Active Technical Decisions
1. **Database Structure** (2024-12-24)
   - Implemented French status labels in models
   - Consolidated migrations for better dependency management
   - Using soft deletes across all major models
   - Polymorphic relationships for activities

2. **Authentication & Authorization** (2024-12-22)
   - Using Spatie Laravel-Permission
   - Roles: super-admin, manager, conseiller, commercial, partenaire
   - Permissions are documented with French descriptions

## Recent Changes (Last Session)
1. **Database Restructuring** (2024-12-24)
   - Updated Client, Prospect, and Activity models
   - Added French status constants
   - Fixed migration order dependencies
   - Status: Completed
   - Files Affected:
     * app/Models/Client.php
     * app/Models/Prospect.php
     * app/Models/Activity.php
     * database/migrations/*_create_consolidated_*.php

2. **Seeder Updates** (2024-12-24)
   - Consolidated RoleSeeder into RolesAndPermissionsSeeder
   - Updated TestDataSeeder for new model structure
   - Added description field to permissions table

## Known Issues
1. **Testing Environment**
   - Some Filament tests failing (403/405 errors)
   - Status: Under Investigation
   - Priority: High

2. **Technical Debt**
   - Need to clean up old migrations
   - Some duplicate permission definitions
   - Status: Tracked, Low Priority

## Next Actions
1. **High Priority**
   - [ ] Fix Filament test issues
   - [ ] Complete permission system implementation
   - [ ] Add missing model validations

2. **Planned Features**
   - [ ] Email notification system
   - [ ] Activity logging improvements
   - [ ] Prospect to client conversion workflow

## Technical Boundaries
1. **Language**
   - All user-facing content must be in French
   - Code comments and documentation in French
   - Database: French for business terms, English for technical terms

2. **Data Model Rules**
   - All major models must implement soft deletes
   - Status fields must use predefined constants
   - Activities must use polymorphic relationships

3. **Testing Requirements**
   - All model changes require unit tests
   - Feature tests for all Filament resources
   - French assertions for user-facing content

## Architecture Decisions
1. **Active**
   - ADR-003: Visual-First Approach (2024-12-25)
   - ADR-002: Database Migrations Cleanup (2024-12-23)
   - ADR-001: Role Management System (2024-12-22)

2. **Pending**
   - Notification System Architecture
   - Audit Log Implementation

## Development Approach
1. **Visual-First Development**
   - All features must follow VISUAL_APPROACH.md guidelines
   - UI/UX prototypes required before implementation
   - Visual documentation with screenshots/videos
   - Interactive demonstrations prioritized

## Session Markers
- Last Session ID: 2024122401
- Last Major Change: Database Models Restructuring
- Current Context Hash: DB_RESTRUCTURE_24122024

## Related Documentation
- Technical Specifications: docs/technical/
- Feature Documentation: docs/features/
- Architecture Decisions: docs/architecture/adr/
- Session Logs: docs/sessions/

## Notes for AI Assistant
1. **Context Preservation**
   - Always check model status constants before modifications
   - Verify migration order for foreign key constraints
   - Maintain French-first approach in user-facing elements

2. **Critical Paths**
   - Permission system is central to all features
   - Activity tracking affects multiple models
   - Status transitions must be carefully managed
