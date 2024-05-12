<?php

namespace Drupal\instagram_importer;

/**
 * Class ImportImgFromUrlServices
 * @package Drupal\instagram_importer\Services
 */
class ImportImgFromUrlServices {

  /**
   * Import the pictures from media and attach him to current media
   *
   * @param int $nbr Desired number of media
   */
  function import(int $nbr)
  {
    // Get x last medias
    $query = \Drupal::entityQuery('media');
      $query->condition('bundle','instagram_posts');
      $query->sort('created', 'DESC');
      $query->range(0, $nbr);

    $mids = $query->execute();
    $medias =  \Drupal\media\Entity\Media::loadMultiple($mids);

    // Store the picture from media's url and attach him to media
    foreach ($medias as $media) {

      $urlThumbnail = $media->field_thumbnail_url->value;

      /**
       * Check if thumbnail exists
       * If yes, it means that the media is a video
       */
      if ($urlThumbnail) {

        $file_info = system_retrieve_file($urlThumbnail, null, TRUE, \Drupal\Core\File\FileSystemInterface::EXISTS_ERROR);

      } else {

        $urlMedia = $media->field_media_url->value;
        $file_info = system_retrieve_file($urlMedia, null, TRUE, \Drupal\Core\File\FileSystemInterface::EXISTS_ERROR);

      }

      if ($file_info->fid) {

        /* Update image field to media */
        $media->field_image->entity = $file_info;
        $media->save();


        \Drupal::logger('Import_instagram')->notice('@type : %result', [
          '@type' => 'imported picture',
          '%result' => $file_info
        ]);
      }
    }
  }
}
