<?php

namespace Arc\Media;

use Arc\Models\Post;

class Media
{
    protected $filePath;

    public function attachFile($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function toPost($post)
    {
        if ($post instanceof Post) {
            $post = $post->ID;
        }

        // We'll need to extract the filename from the path
        $filename = basename($this->filePath);

        // First we need to upload the file
        $uploadedFile = wp_upload_bits(
            $filename, null, file_get_contents($this->filePath)
        );

        // If there was an error uploading the file we need to handle it
        if ($uploadedFile['error']) {
            throw new \Exception(
                'Error uploading ' . $this->filePath . ': ' . $uploadedFile['error']
            );
        }

        // Then we need to get the mimetype of the original file
        $mimeType = wp_check_filetype($this->filePath);

        // Prepare the attachment data
        $attachment = [
            'post_mime_type' => $mimeType['type'],
            'post_parent' => $post,
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        // Attach the file to the post
        $attachmentId = wp_insert_attachment($attachment, $uploadedFile['file'], $post);

        // If there is an error we must handle it
        if (is_wp_error($attachmentId)) {
            throw new \Exception($attachmentId);
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachmentData = wp_generate_attachment_metadata($attachmentId, $uploadedFile['file']);
        wp_update_attachment_metadata($attachmentId,  $attachmentData);
    }
}
