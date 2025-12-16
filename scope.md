Smart Site — Admin & Client Portals (Views → Requirements Map)

This document translates each screen/view in the design into concrete, testable requirements so engineering and QA can implement and verify behavior consistently. Source: Smart Site design deck.  ￼

⸻

0) Global
	•	Branding & shells: “SS / SMART SITE” header, powered-by footer, left navigation with modules: Dashboard, Users, Forms, Reports, Data, Workforce. System Admin has Users, Clients, Statement, Dashboard.  ￼
	•	Session messages & states: “Logged out successfully!” banner; disabled actions for unauthorized roles; empty-state cards (no projects/forms/data yet).  ￼
	•	Tables: server-side pagination, keyword search, column filters, CSV/XLSX export (where relevant).
	•	RBAC:
	•	System Administrator (platform) → system users, clients, statements, global dashboard.
	•	Tenant Admin → all tenant modules.
	•	Manager → read/write within assigned projects.
	•	Mobile User → mobile-only functionality (read assigned WOs, submit records).
	•	Audit all create/update/delete actions with actor, time, IP/UA.

⸻

1) Authentication & Onboarding

1.1 Login / Logout
	•	Fields: email, password; actions: Log in, “Forget Password”. Success redirects to last module visited. Logout returns to login with success banner.  ￼

1.2 Forgot Password Flow
	•	Step A: enter email → show message “A verification code has been sent to your email sa……@hotmail.com”.
	•	Step B: insert 6-digit code (with “Verify” action).
	•	Step C: new password + confirm password → success returns to Login. Error: invalid/expired code.  ￼

1.3 Create Account (Client Self-Serve)
	•	Fields: first name, last name, company name, field of work (select), country (select), phone, email, password, confirm password, captcha text.
	•	CTA: Create an account; deep link “Already have an account? Sign In”.
	•	Why use Smart Site info panel shown.  ￼

1.4 Payment & Billing (Sign-Up Completion)
	•	Payment Summary screen then Billing Address (first/last/street/city/country/zip/phone) → Payment Information (card number, expiry, name, CVV, save for auto-renew).
	•	Payment Options management: list masked cards (set default, remove). Errors for invalid card, missing required fields.  ￼

⸻

2) System Administrator Console

2.1 System Admin Dashboard
	•	Quick metrics panels (counts, trends TBD), navigation to Users, Clients, Statement.  ￼

2.2 Users (System)
	•	List: search, role filter.
	•	New User modal: email, auto-generated password (view/copy), first name, last name, role, phone, captcha text, “Send confirmation email”, “User will be asked to update password at first login”. Save / Close. Validation: unique email, valid phone, required names.  ￼

2.3 Clients
	•	List: client cards with Company Name, active/inactive/suspended user counts, storage used MB, total payments, field of work, account creation date; actions: Statement, Close.
	•	Client Statement: pick client → Total Amount (computed), line items export.  ￼

⸻

3) Tenant (Client) Portal

3.1 Tenant Dashboard
	•	Date filter (From/To) + Projects strip (Project 1..4 + add). Two layouts are shown (MM/DD and DD/MM), the product must support locale formatting.  ￼
	•	Widgets overlay: select Project → add “Quarterly Progress”, “Monthly Progress”, “Percentage Complete”, “Man Power”, “Progress by Form”. Each widget has Customize (filters: user, form, month range, group by) and Add/Close. Persist widget layout per user.  ￼

3.2 My Profile
	•	Fields: first/last, country, phone, work email (read-only or editable per policy), password + confirm, Company Name & Field of Work displayed with note: “To update Company name and Field of Work, contact admin@smartkodes.com”.
	•	Account created on [date], “Last updated on [date]”.
	•	Deactivation banner: “You may re-activate your account within 60 days to keep your data available.”  ￼

⸻

4) Users Module (Tenant)

4.1 Overview Page
	•	KPIs: System Users count, Mobile Users count. Tabs or tiles: System Users, Mobile Users, User Groups, Payments.  ￼

4.2 Create / Edit System User
	•	Fields: email, auto password, first/last, role, phone, check “Send confirmation email”, note “User will be asked to update the password upon confirmation”. Save / Close. Validation: unique email within tenant.  ￼

4.3 Create / Edit Mobile User
	•	As above + Activate User toggle and Group select (optional).  ￼

4.4 User Groups
	•	Fields: group name, description, user multi-select; Save / Close.
	•	On save, members inherit project access based on project memberships.  ￼

4.5 Payments (Tenant)
	•	Mirror of payment options management (add card, set default, remove) and invoice list.  ￼

⸻

5) Projects Module

5.1 Projects List
	•	KPIs: Running Projects, Completed Projects counts with tabs. Search, filters (status, manager). Actions: New Project, Assign Forms.  ￼

5.2 New Project
	•	Fields: Project Name (text), Description, Client (select), Area (text), Start Date (date picker). Save / Close.
	•	Validation: name 3–120 chars, start date ≥ today (configurable), client required if multi-client tenants.  ￼

5.3 Assign Forms to Project
	•	Left panel: available Forms. Right panel: “Assigned Forms” drop-zone. Drag & drop to assign/remove, persist order. Save / Close.  ￼

⸻

6) Forms Module

6.1 Forms List
	•	KPIs: Active Forms / Inactive Forms with Filter; list with status, last updated, field count; actions: Create, Edit, Delete, Export Template.  ￼

6.2 Form Builder
	•	Palette types: Text, Number, Password, Check Box, Drop Down, ON/OFF, YES/NO, List, Option List, Date, Time, File, Image, URL, Audio, Video, Barcode, GPS Location, Currency.
	•	Field Properties: Field Name, Type, Default Value, Hint, Required, Enabled (+ type-specific options e.g., Date format, Time format 12/24h, max duration for Audio/Video, option lists, multi-select).
	•	Commands: Create / Save / Delete / Save and Publish / Export Template.
	•	Versioning UI: “Update Form: [Form Name] — Draft v1.x”. Publishing creates a new version.  ￼

⸻

7) Workforce (Work Orders)

7.1 Batches & Assignment
	•	Step 1: Select Project → message: “Select a Batch to be able to Create / Import Work Orders to display here”.
	•	Step 2: Select Batch (e.g., “Batch 3”) → Create / Import Work Orders appears.
	•	Create/Assign Form: select Form (required), Mobile User (required), optional Field Value overrides, Location (Lat/Long), field type selector for dynamic fields; Assign action.
	•	Import Data: multi-step import wizard → Import Valid Records (show counts), then Assign.  ￼

⸻

8) Data Module (Browsing & Download)

8.1 Browse Records
	•	Filters: Projects, Forms (multi), Fields picker (with “Select All”).
	•	Record card shows: ID, Mobile User, Date, field-value pairs, media previews (Image/Video/Audio), Voice Message, Reference URL. Zoom control for media (− 100% +).  ￼

8.2 Data Download
	•	Dialog: Project select, Form select, Select Local Directory (desktop native chooser), checkbox Download the data (tabular, images, audio and video) from all Forms. Action: Download bundles CSV/JSON + media folders.  ￼

⸻

9) Reports Module
	•	Inputs: choose Projects and Forms, pick Fields (with “Select All”), Generate.
	•	Output: tabular report with selected fields; export CSV/XLSX.  ￼

⸻

10) Mobile App (Field User)

10.1 Login & Setup
	•	Options: Login by email; Set a passcode screen; Verify passcode; Settings toggles: “Use mobile data to upload files”, “Use Wi-Fi to upload files”, “Show notifications on screen”. Defaults: Wi-Fi on, Data off.  ￼

10.2 Work Orders List
	•	Header: “WORK ORDERS”, totals (“Total work orders: x”, “High priority: y”). Sorting: Distance or Priority; show dist/time (e.g., “Distance: 1.6 km / 22 min”). List cell shows Form name + two key field lines.
	•	Actions: open WO → form detail with dynamic controls according to assigned Form fields (all field types supported); inline camera/gallery, barcode scan, GPS capture, file pickers, signatures (if required).
	•	States: “Data saved locally!”, “Data uploaded successfully!”. Offline queue auto-syncs when connection meets Settings policy.  ￼

10.3 Collected Data
	•	Separate list with sorting by Date/Time or Priority and a total records counter. Tapping opens record detail with a Gallery for media, map for GPS, and references.  ￼

10.4 Manage Forms (Mobile)
	•	Table shows Form Name, Version, Fields count with Update / Delete / Download / Request actions as applicable (download = sync latest).  ￼

⸻

11) Validation & UX Rules (Cross-cutting)
	•	Required markers on all mandatory inputs; disable primary CTA until valid.
	•	Confirm modals for destructive actions (Delete Form, Remove Assigned Form, Remove Payment Method).
	•	Publish vs Draft (Forms): “Save” retains draft; “Save and Publish” versions the form and updates projects using it.
	•	Uploads: enforce type/size caps (per field type); show progress; retry on failure; dedupe by checksum where possible.
	•	Time & date: respect user timezone & locale for display; store UTC.

⸻

12) Acceptance Criteria (Per Module)
	•	Auth: user can reset password via email code and log in successfully; invalid code blocked with clear error; logout shows success banner.  ￼
	•	System Admin: can create a System User (email sent), view Clients with stats, open a client Statement and see total amount.  ￼
	•	Dashboard: user can set date range, add at least 3 widgets to a project, customize one widget, and see persisted layout after reload.  ￼
	•	Users (Tenant): create System and Mobile users, activate/deactivate, assign to User Groups; Payments tab lists saved cards and allows default switch/remove.  ￼
	•	Projects: create new project (name/description/client/area/date), list shows Running/Completed, and Assign Forms via drag & drop then saved.  ￼
	•	Forms: build a form using at least 5 field types (incl. media, barcode, GPS), set properties, Save and Publish, export template JSON.  ￼
	•	Workforce: select project & batch; import CSV; see Import Valid Records; assign work orders to a mobile user; confirm appear on user’s device.  ￼
	•	Data: filter by form & project; open a record and preview image/video/audio/GPS; Data Download produces bundle with tabular + media.  ￼
	•	Reports: select forms/projects/fields; generate and export CSV/XLSX; “Select All” toggles all fields.  ￼
	•	Mobile: see Work Orders list with distance/priority sort, complete a record offline, see “Data saved locally!”, later “uploaded successfully” when online; Manage Forms shows versions and counts.  ￼

⸻

13) Non-Functional
	•	Performance: P95 < 300 ms on list endpoints with 25 items/page.
	•	Accessibility: keyboard focus states, labels for inputs, sufficient contrast.
	•	Localization: dates “29-Dec-2022 / 29-Jan-2023” and “December 29, 2022 / January 29, 2023” formats both supported based on locale.  ￼
	•	Security: RBAC enforced on every API; tenant scoping; audit logs; rate limiting on auth & file uploads.

⸻

14) Open Questions (to finalize)
	1.	Project fields: do we add end date, SLA defaults, geofence now or phase-2? (Design shows minimal.)  ￼
	2.	Payments scope in tenant portal: invoices list & PDF download? (Design shows card mgmt.)  ￼
	3.	Reports export format: CSV & XLSX confirmed; PDF needed?
	4.	Form versioning: should publishing lock edits to older versions & auto-migrate assigned projects?

￼
