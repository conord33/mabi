<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../Model.php';

use MABI\Model;

class Session extends Model {

  /**
   * Identifies the authenticated session. This id is included in the request header for various endpoints
   * to identify which session the client is using.
   *
   * @field id
   * @var string
   */
  public $sessionId;

  /**
   * When the session was created
   *
   * @var \DateTime
   * @field internal
   */
  public $created;

  /**
   * The last time the session was accessed. This is updated whenever the session is read from the header
   * if using the \MABI\Identity\SessionHeader middleware.
   *
   * @field internal
   * @var \DateTime
   */
  public $lastAccessed;

  /**
   * The ID of the user which this session is authenticated for
   *
   * @var string
   * @field owner
   */
  public $userId;

  /**
   * The full user object which this session is authenticated for. This is only returned when creating a session
   * so that an extra request does not need to be made to the User controller. This is most likely wasteful for other
   * requests so only the userId field is filled.
   *
   * @var User
   * @field external
   */
  public $user;

  /**
   * The email that is used to authenticate the user. This should only be filled for incoming POSTs to create
   * new sessions. Otherwise it will always be NULL.
   *
   * @var string
   * @field external
   */
  public $email;

  /**
   * The plaintext password that is used to authenticate the user. This should only be filled for incoming POSTs
   * to create new sessions. Otherwise it will always be NULL.
   *
   * @var string
   * @field external
   */
  public $password;
}
