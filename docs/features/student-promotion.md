# Student Promotion (Single-level)

This feature allows Admins and Parents (for their own children) to promote a student by exactly one academic level. Promotions are audited and recorded in `student_promotions` table.

Key points:
- Only Admins or Parents of the student can promote.
- Promotion is single-level only: the next level is determined by `AcademicLevel.grade_number`.
- A promotion creates an audit record in `student_promotions` and updates the student's `academic_level_id` in a transaction.
- Notifications are sent to the student and the primary parent.

Fields added:
- `student_promotions`: `id`, `student_id`, `from_academic_level_id`, `to_academic_level_id`, `promoted_by_id`, `promoted_by_type`, `reason`, `timestamps`

How it works:
1. Admin or Parent initiates Promote action on Student in Admin/Parent UI.
2. System validates the next level exists.
3. Transaction: create `student_promotions` record and update student's `academic_level_id`.
4. Notifications are dispatched to the student and parent.

Recovery & safety:
- Before migration, create DB backups (mysqldump or other backup tool).
- Migrations are reversible; `student_promotions` can be rolled back without changing `students` data.
- If migration fails, restore from backup.

