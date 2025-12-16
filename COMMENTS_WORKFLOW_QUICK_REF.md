# Comments & Workflow - Quick Reference Guide

## 🚀 Quick Start

### Adding a Comment
1. Navigate to any record detail page
2. Find the "Comments & Discussion" panel
3. Type your comment in the text area
4. Optionally check "Internal note" for staff-only visibility
5. Click "Add Comment"

### @Mentioning Users
1. While typing a comment, type `@`
2. A dropdown will appear with available users
3. Start typing a name to filter
4. Click a user or press Enter to mention them
5. Mentioned users will receive notifications (Phase 2 Priority 4)

### Replying to Comments
1. Find the comment you want to reply to
2. Click the "Reply" button below the comment
3. Type your reply
4. Click "Reply" or "Cancel"
5. Replies appear nested under parent comments

### Requesting Approval
1. Open a record detail page
2. Click "Request Approval" button in the Actions panel
3. Select one or more approvers (in order)
4. Click "Request Approval"
5. Approvers will see pending requests

### Approving a Record
1. Open a record where you're listed as approver
2. Find your pending approval in the "Approval Status" panel
3. Click "Approve"
4. Add optional comments
5. Click "Confirm Approval"
6. Record auto-updates to "approved" when all approvals are complete

### Rejecting a Record
1. Open a record where you're listed as approver
2. Find your pending approval in the "Approval Status" panel
3. Click "Reject"
4. Enter **required** rejection reason
5. Click "Confirm Rejection"
6. Record status immediately changes to "rejected"

### Assigning Records
1. Open a record detail page
2. Click "Assign to User" button
3. Select a user from the dropdown
4. Click "Assign"
5. Assignment change is logged in activity timeline

---

## 📋 Feature Overview

### Comments System
- **Add Comments**: Post notes and updates on records
- **Threaded Replies**: Nested conversation threads
- **@Mentions**: Tag team members for notifications
- **Internal Notes**: Staff-only visibility option
- **Delete Comments**: Authors can delete their own comments
- **Edit Protection**: Only comment authors can delete

### Approval Workflow
- **Multi-Step Approvals**: Sequential approval chains
- **Multiple Approvers**: Assign to multiple users
- **Approval Sequence**: Order-based workflow
- **Approval Comments**: Add notes when approving/rejecting
- **Auto Status Update**: Status changes when all approve
- **Rejection Comments**: Required reason for rejection
- **Delegation Support**: Transfer approval to another user (backend ready)

### Activity Timeline
- **Complete History**: All actions logged
- **User Attribution**: Who did what and when
- **Change Tracking**: Before/after values
- **System Actions**: Automated changes tracked
- **Action Types**: Created, Updated, Status Changed, Assigned, Commented, Approved, Rejected, Approval Requested

---

## 🎯 Common Use Cases

### Use Case 1: Team Collaboration
**Scenario:** Field technician submits inspection form, needs office review

1. Technician submits form (status: "submitted")
2. Supervisor reviews and adds comment: "@jane please check the measurements"
3. Jane (engineer) replies: "Measurements look good, approved"
4. Supervisor clicks "Approve"
5. All changes logged in activity timeline

### Use Case 2: Multi-Level Approval
**Scenario:** Expense report requires manager and director approval

1. Employee submits expense report
2. Employee clicks "Request Approval"
3. Selects manager first, then director
4. Manager approves with comment: "Budget approved"
5. Director sees pending approval
6. Director approves with comment: "Processing payment"
7. Status auto-changes to "approved"

### Use Case 3: Rejection with Feedback
**Scenario:** Safety inspection fails requirements

1. Inspector submits safety form
2. Safety officer reviews
3. Officer clicks "Reject"
4. Enters reason: "Missing fire extinguisher inspection dates"
5. Status changes to "rejected"
6. Inspector receives notification (Phase 2 Priority 4)
7. Inspector can view rejection reason and re-submit

### Use Case 4: Internal Discussion
**Scenario:** Team discusses sensitive client information

1. Account manager adds comment
2. Checks "Internal note" checkbox
3. Comment only visible to staff members
4. Client doesn't see internal discussion
5. Team can coordinate privately

### Use Case 5: Reassignment
**Scenario:** Workload balancing

1. Manager reviews record queue
2. Notices record assigned to busy technician
3. Clicks "Assign to User"
4. Selects available technician
5. Assignment logged in timeline
6. New technician sees record in their queue

---

## 💡 Tips & Best Practices

### Comments
- ✅ Use @mentions to notify specific users
- ✅ Mark sensitive discussions as "Internal"
- ✅ Reply to specific comments for context
- ✅ Be clear and concise
- ❌ Don't share confidential info in public comments
- ❌ Don't use comments for approvals (use approval workflow)

### Approvals
- ✅ Request approvals in logical order (e.g., supervisor → manager → director)
- ✅ Add helpful comments when approving
- ✅ Always provide detailed rejection reasons
- ✅ Review activity timeline before approving
- ❌ Don't skip sequence (approvals are sequential)
- ❌ Don't approve without reviewing the data

### Activity Timeline
- ✅ Review timeline to understand record history
- ✅ Use timeline to track who made changes
- ✅ Reference timeline in audits
- ✅ Check timeline before major actions
- ❌ Timeline cannot be edited (immutable log)
- ❌ Don't delete records with important timeline history

---

## 🔒 Security & Permissions

### Comment Permissions
- **Add Comment**: Any authenticated user
- **Delete Comment**: Only comment author or admin
- **Reply to Comment**: Any authenticated user
- **View Internal Notes**: Staff only (non-internal visible to all)

### Approval Permissions
- **Request Approval**: Any authenticated user
- **Approve/Reject**: Only assigned approver or delegate
- **View Approvals**: All users can see approval status
- **Delegate Approval**: Approver only (UI pending)

### Assignment Permissions
- **Assign Record**: Manager or admin role
- **View Assignments**: All users
- **Self-Assign**: Configurable per tenant

### Activity Log Permissions
- **View Timeline**: All users with record access
- **Edit Timeline**: None (immutable)
- **Delete Timeline**: None (permanent audit trail)

---

## 🎨 UI Components

### Comments Panel (Left Side)
```
┌─────────────────────────────────┐
│ Comments & Discussion           │
├─────────────────────────────────┤
│ [Add Comment Form]              │
│ ☐ Internal note (staff only)   │
│                     [Add Comment]│
├─────────────────────────────────┤
│ 👤 John Doe • 2 hours ago       │
│    This looks good! @jane       │
│    [Reply]                      │
│    └─ 👤 Jane • 1 hour ago      │
│       I agree!                  │
└─────────────────────────────────┘
```

### Activity Timeline (Right Side)
```
┌─────────────────────────────────┐
│ Activity Timeline               │
├─────────────────────────────────┤
│ 🔵 John created record          │
│    2 hours ago                  │
│                                 │
│ ✏️ Jane updated status          │
│    submitted → in_review        │
│    1 hour ago                   │
│                                 │
│ 💬 John commented               │
│    30 minutes ago               │
│                                 │
│ ✅ Jane approved                │
│    5 minutes ago                │
└─────────────────────────────────┘
```

### Approval Status (Sidebar)
```
┌─────────────────────────────────┐
│ Approval Status                 │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐ │
│ │ Manager (Step 1)            │ │
│ │ ✅ Approved                 │ │
│ │ "Budget approved"           │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ Director (Step 2)           │ │
│ │ ⏳ Pending                  │ │
│ │ [Approve] [Reject]          │ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

---

## 🔧 Technical Details

### Database Tables

#### record_comments
- `id` - ULID primary key
- `tenant_id` - Multi-tenant isolation
- `record_id` - Associated record
- `user_id` - Comment author
- `parent_id` - For threaded replies
- `comment` - Comment text
- `mentions` - JSON array of mentioned user IDs
- `attachments` - JSON array of file references
- `is_internal` - Staff-only flag
- `created_at`, `updated_at`, `deleted_at`

#### record_activities
- `id` - ULID primary key
- `tenant_id` - Multi-tenant isolation
- `record_id` - Associated record
- `user_id` - Actor (null for system)
- `action` - Action type
- `field_name` - Changed field
- `old_value` - Before value
- `new_value` - After value
- `description` - Human-readable description
- `metadata` - JSON additional data
- `created_at` (no updated_at - immutable)

#### record_approvals
- `id` - ULID primary key
- `tenant_id` - Multi-tenant isolation
- `record_id` - Associated record
- `approver_id` - Assigned approver
- `requested_by` - Who requested approval
- `sequence` - Order in approval chain
- `status` - pending, approved, rejected, delegated
- `comments` - Approval/rejection notes
- `approved_at` - Approval timestamp
- `rejected_at` - Rejection timestamp
- `delegated_to` - Delegated user ID
- `created_at`, `updated_at`

### Routes

#### Comments
- `POST /admin/records/{record}/comments` - Add comment
- `DELETE /admin/records/{record}/comments/{comment}` - Delete comment

#### Approvals
- `POST /admin/records/{record}/request-approval` - Request approval
- `POST /admin/records/{record}/approvals/{approval}/approve` - Approve
- `POST /admin/records/{record}/approvals/{approval}/reject` - Reject

#### Assignment
- `POST /admin/records/{record}/assign` - Assign to user

All routes have tenant equivalents with `/tenant` prefix.

### API Endpoints (Future)
```php
// Phase 3 - API Support
GET    /api/records/{id}/comments          // List comments
POST   /api/records/{id}/comments          // Add comment
DELETE /api/records/{id}/comments/{id}     // Delete comment
GET    /api/records/{id}/activities        // Activity timeline
POST   /api/records/{id}/approve           // Approve
POST   /api/records/{id}/reject            // Reject
```

---

## 📊 Metrics & Reporting

### Available Metrics (Phase 3)
- Average approval time
- Rejection rate by form type
- Comment activity by user
- Approval bottlenecks
- Most active commenters
- Timeline completeness
- Assignment workload distribution

---

## 🐛 Troubleshooting

### Comment Not Appearing
**Problem:** Added comment doesn't show up
**Solutions:**
1. Check if page auto-refreshed
2. Verify user has permission
3. Check browser console for JavaScript errors
4. Ensure tenant context is set

### @Mention Not Working
**Problem:** Dropdown doesn't appear when typing @
**Solutions:**
1. Check if JavaScript loaded correctly
2. Verify users exist in tenant
3. Try typing @ at start of line
4. Ensure no spaces after @

### Approval Button Disabled
**Problem:** Can't click Approve/Reject
**Solutions:**
1. Verify you're the assigned approver
2. Check if already approved/rejected
3. Confirm approval sequence (must be in order)
4. Check user permissions

### Activity Timeline Empty
**Problem:** No activities showing
**Solutions:**
1. Record may be newly created
2. Check if activities are logged (database)
3. Verify eager loading in controller
4. Check tenant filtering

### Assignment Not Saving
**Problem:** User assignment doesn't persist
**Solutions:**
1. Verify user exists in same tenant
2. Check permissions (manager/admin role)
3. Review validation errors
4. Check database constraints

---

## 🚧 Known Limitations (Current)

1. **No Email Notifications**: Mentioned users don't receive emails (Phase 2 Priority 4)
2. **No Real-time Updates**: Page refresh required to see new comments
3. **No Rich Text**: Comments are plain text only
4. **No File Attachments in Comments**: Structure exists but UI pending
5. **No Approval Delegation UI**: Backend ready, UI not built
6. **No Comment Editing**: Can only delete and re-add
7. **No Search in Comments**: Full-text search not implemented
8. **No Comment Reactions**: Like/emoji reactions not available

---

## 🔮 Future Enhancements

### Phase 3
- [ ] Email notifications for mentions
- [ ] Real-time comment updates (WebSockets/Pusher)
- [ ] Rich text editor (TinyMCE/Quill)
- [ ] File attachments in comments
- [ ] Approval delegation UI
- [ ] Comment editing (with edit history)
- [ ] Comment search and filtering
- [ ] Approval workflow templates

### Phase 4
- [ ] Comment reactions (👍 ❤️ 😊)
- [ ] @team mentions (entire teams)
- [ ] Private messages
- [ ] Comment threading depth limit
- [ ] Export activity timeline to PDF
- [ ] Bulk approval actions
- [ ] Conditional approval routing
- [ ] SLA tracking and reminders

---

## 📚 Related Documentation

- [PHASE_2_PRIORITY_5_COMPLETE.md](./PHASE_2_PRIORITY_5_COMPLETE.md) - Complete technical documentation
- [PHASE_2_IMPLEMENTATION.md](./PHASE_2_IMPLEMENTATION.md) - Overall Phase 2 plan
- [PHASE_2_PROGRESS_SUMMARY.md](./PHASE_2_PROGRESS_SUMMARY.md) - Progress tracking

---

## 💬 Support

For questions or issues:
1. Review this guide thoroughly
2. Check technical documentation
3. Review activity timeline for clues
4. Contact system administrator
5. Submit bug report with screenshots

---

**Last Updated:** October 5, 2025  
**Version:** 1.0 (UI Complete)  
**Status:** Production Ready (Backend + UI)
