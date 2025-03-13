INSERT INTO artworks (title, description, file_path, artist_id, category_id, status, upload_date)
SELECT 
    CONCAT('Artwork ', SUBSTRING_INDEX(file_path, '/', -1)),
    'Beautiful artwork',
    file_path,
    (SELECT user_id FROM users WHERE role = 'admin' LIMIT 1),
    (SELECT category_id FROM categories ORDER BY RAND() LIMIT 1),
    'approved',
    NOW()
FROM (
    SELECT CONCAT('uploads/artworks/', filename) as file_path
    FROM (
        SELECT 'DJI_0001.JPG' as filename UNION ALL
        SELECT 'DJI_0002.JPG' UNION ALL
        SELECT 'DJI_0004.JPG' UNION ALL
        SELECT 'DJI_0005.JPG' UNION ALL
        SELECT 'DJI_0010.JPG' UNION ALL
        SELECT 'DJI_0011.JPG' UNION ALL
        SELECT 'DJI_0012.JPG' UNION ALL
        SELECT 'DJI_0013.JPG' UNION ALL
        SELECT 'DJI_0016.JPG' UNION ALL
        SELECT 'DJI_0017.JPG' UNION ALL
        SELECT 'DJI_0018.JPG' UNION ALL
        SELECT 'DJI_0019.JPG' UNION ALL
        SELECT 'DJI_0020.JPG' UNION ALL
        SELECT 'DJI_0021.JPG' UNION ALL
        SELECT 'DJI_0022.JPG' UNION ALL
        SELECT 'DJI_0023.JPG' UNION ALL
        SELECT 'DJI_0024.JPG' UNION ALL
        SELECT 'DJI_0026.JPG' UNION ALL
        SELECT 'DJI_0029.JPG' UNION ALL
        SELECT 'DJI_0030.JPG' UNION ALL
        SELECT 'DJI_0031.JPG' UNION ALL
        SELECT 'DJI_0032.JPG' UNION ALL
        SELECT 'DJI_0033.JPG'
    ) as filenames
) as paths; 