-- Check all feedback entries
SELECT 
    f.feedback_id,
    f.booking_id,
    b.full_name,
    f.rating,
    f.comment,
    f.photo_path,
    f.video_path,
    b.package_id
FROM feedback f
LEFT JOIN bookings b ON f.booking_id = b.booking_id
ORDER BY f.feedback_id DESC; 