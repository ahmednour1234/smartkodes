# Bulk Operations Quick Reference

## Quick Actions

### Export Records to CSV
1. ☑️ Check records to export
2. Click **📥 Export Selected**
3. CSV downloads automatically

### Update Status
1. ☑️ Select records
2. Click **✏️ Change Status**
3. Choose status from dropdown
4. Click **Update Status**

### Delete Records
1. ☑️ Select records
2. Click **🗑️ Delete Selected**
3. Confirm deletion

---

## Routes

| Action | Method | URL | Controller Method |
|--------|--------|-----|-------------------|
| Export | POST | `/admin/records/bulk-export` | `bulkExport()` |
| Update Status | POST | `/admin/records/bulk-update-status` | `bulkUpdateStatus()` |
| Delete | DELETE | `/admin/records/bulk-delete` | `bulkDelete()` |

---

## JavaScript Functions

```javascript
// Get selected record IDs
getSelectedRecords()

// Update UI
updateBulkActionsBar()
toggleSelectAll(checkbox)
clearSelection()

// Actions
bulkExport()
showBulkStatusModal()
bulkDelete()
```

---

## Status Options

- `submitted` - Submitted
- `in_review` - In Review
- `approved` - Approved
- `rejected` - Rejected
- `pending_info` - Pending Information

---

## CSV Export Format

```csv
Record ID,Form,Project,Status,Submitted By,Submitted At,Location,[Field 1],[Field 2],...
01JBRZT...,Safety Inspection,Site A,Approved,John Doe,2025-01-15 10:30:00,"40.7128, -74.0060",Yes,Good,...
```

---

## Security Notes

- ✅ Tenant-scoped queries
- ✅ CSRF token required
- ✅ Record ID validation
- ✅ Status value validation
- ✅ Confirmation for destructive actions

---

## Common Issues

**Q: Selection not persisting across pages?**  
A: By design. Selections are page-specific. Export/update records on current page, then move to next page.

**Q: CSV encoding issues?**  
A: Use UTF-8 encoding when opening in Excel. Use "Import Data" instead of double-clicking.

**Q: Large export timing out?**  
A: Export smaller batches (≤500 records at a time).

---

## Testing Checklist

- [ ] Select individual records
- [ ] Select all records on page
- [ ] Export CSV with correct data
- [ ] Update status for multiple records
- [ ] Delete multiple records
- [ ] Clear selection works
- [ ] Confirmation dialogs appear
- [ ] Count updates correctly

---

**Files:** `resources/views/admin/records/index.blade.php`, `RecordController.php`  
**Documentation:** [PHASE_2_PRIORITY_2_COMPLETE.md](./PHASE_2_PRIORITY_2_COMPLETE.md)
