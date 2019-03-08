<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AdminsGroups;
use App\Models\Admins;

use App\Models\Groups;
use App\Models\SubGroups;
use App\Models\LevelGroups;
use App\Models\Courses;
use App\Models\Categories;
use App\Models\Topics;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\Instructors;
use App\Models\Documents;
use App\Models\Quiz;
use App\Models\ClassRooms;
use App\Models\Slides;
use App\Models\Orders;

use Auth;

class _RolesController extends Controller
{
    // var $user;
    public $_errorCode;
    protected $_user;
    private $_superId;
    // private $_result;

    public function __construct()
    {
        $this->_errorCode = null;
        $this->_superId = 1;
        $this->_user = Auth::user();
        $this->_admins_groups = $this->_user->admins_groups()->first();
        if ($this->_admins_groups) {
            $this->_authSessionGroups = $this->_admins_groups->groups()->get();
        } else {
            $this->_authSessionGroups = [];
        }
        $this->_authSessionLevelGroups = $this->_user->admin2level_group()->get();
    }

    private function setSuccess()
    {
        $this->_errorCode = null;
        return true;
    }

    private function setError($code)
    {
        $this->_errorCode = $code;
        return false;
    }

    private function checkExist($id)
    {
        $admin = Admins::find($id);
        if (!$admin) { return false; }
        return true;
    }

    public function setUser($user)
    {
        $this->_user = $user;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function isSuper()
    {
        if ($this->_user->admins_groups_id != $this->_superId) {
            return $this->setError(403);
        }

        return $this->setSuccess();
    }

    public function haveAccess($id, $entity, $task = null)
    {
        switch (strtolower($entity)) {
            case 'admins':
                if ($this->_user->id != $id) {

                    if ($this->_user->super_users) {
                        return $this->setError(403);
                    } else if(!$this->isSuper()) {
                        $data = new Admins;
                        $data = $data->where('id', $id);
                        $data = $data->whereHas('groups', function($query) {
                            $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                        });

                        if ($data->count() != 1) {
                            return $this->setError(403);
                        }
                    }

                }

                break;
                // End Admins

            case 'groups':
                if (!empty($id)) {
                    if ($this->_user->super_users) {
                        if ($this->_user->groups_id != $id) {
                            return $this->setError(403);
                        }
                    } else if(!$this->isSuper()) {
                        $dataMatched = array_first($this->_authSessionGroups, function ($value, $key) use ($id) {
                            return $value->id == $id;
                        });

                        if (!$dataMatched) {
                            return $this->setError(403);
                        }
                    }
                } else {
                    switch (strtolower($task)) {
                        case 'store':
                            if (!$this->isSuper()) {
                                return $this->setError(403);
                            }
                            break;

                        case 'destroy':
                            if (!$this->isSuper()) {
                                return $this->setError(403);
                            }
                            break;

                        default:
                            # code...
                            break;
                    }
                }

                break;
                // End Groups

            case 'sub_groups':
                if ($this->_user->super_users) {
                    return $this->setError(403);
                } else if(!$this->isSuper()) {
                    $data = new SubGroups;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Sub Groups

            case 'level_groups':
                if ($this->_user->super_users) {
                    $data = new LevelGroups;
                    $data = $data->where('id', $id);
                    $data = $data->where('admins_id', $this->_user->id);

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new LevelGroups;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Level Groups

            case 'courses':
                if ($this->_user->super_users) {
                    $data = new Courses;
                    $data = $data->where('id', $id);
                    $data = $data->where('admins_id', $this->_user->id);

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Courses;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Courses

            case 'categories':
                if ($this->_user->super_users) {
                    $data = new Categories;
                    $data = $data->where('id', $id);
                    $data = $data->where('admins_id', $this->_user->id);

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Categories;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Categories

            case 'topics':
                if ($this->_user->super_users) {
                    $data = new Topics;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->where('admins_id', $this->_user->id);
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Topics;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->whereHas('groups', function($subQuery) {
                            $subQuery->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                        });
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Topics

            case 'members':
                if ($this->_user->super_users) {
                    $data = new Members;
                    $data = $data->where('id', $id);
                    $data = $data->where('sub_groups_id', $this->_user->sub_groups_id);
                    $data = $data->whereHas('level_groups', function($query) {
                        $query->where('admins_id', $this->_user->id);
                        $query->where('groups_id', $this->_user->groups_id);
                        // $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    $access = new Members;
                    $access = $access->where('id', $id);
                    $access = $access->where('sub_groups_id', $this->_user->sub_groups_id);
                    $access = $access->whereHas('level_groups', function($query) {
                        $query->whereIn('level_groups_id', array_pluck($this->_authSessionLevelGroups, 'id'));
                    });

                    if ($data->count() != 1 && $access->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Members;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Members

            case 'members_pre_approved':
                if ($this->_user->super_users) {
                    $data = new MembersPreApproved;
                    $data = $data->where('id', $id);
                    $data = $data->where('sub_groups_id', $this->_user->sub_groups_id);
                    $data = $data->whereHas('level_groups', function($query) {
                        $query->where('admins_id', $this->_user->id);
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new MembersPreApproved;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Members Pre-Approved

            case 'instructors':
                if ($this->_user->super_users) {
                    $data = new Instructors;
                    $data = $data->where('id', $id);
                    $data = $data->where('admins_id', $this->_user->id);

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Instructors;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Instructors

            case 'documents':
                if ($this->_user->super_users) {
                    $data = new Documents;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->where('admins_id', $this->_user->id);
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Documents;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->whereHas('groups', function($subQuery) {
                            $subQuery->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                        });
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Documents

            case 'quiz':
                if ($this->_user->super_users) {
                    $data = new Quiz;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->where('admins_id', $this->_user->id);
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Quiz;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->whereHas('groups', function($subQuery) {
                            $subQuery->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                        });
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Quiz

            case 'classrooms':
                if ($this->_user->super_users) {
                    $data = new ClassRooms;
                    $data = $data->where('id', $id);
                    $data = $data->where('admins_id', $this->_user->id);

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new ClassRooms;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Class Rooms

            case 'slides':
                if ($this->_user->super_users) {
                    $data = new Slides;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->where('admins_id', $this->_user->id);
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                } else if(!$this->isSuper()) {
                    $data = new Slides;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('courses', function($query) {
                        $query->whereHas('groups', function($subQuery) {
                            $subQuery->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                        });
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Slides

            case 'orders':
                if ($this->_user->super_users) {
                    return $this->setError(403);
                } else if(!$this->isSuper()) {
                    $data = new Orders;
                    $data = $data->where('id', $id);
                    $data = $data->whereHas('groups', function($query) {
                        $query->whereIn('groups_id', array_pluck($this->_authSessionGroups, 'id'));
                    });

                    if ($data->count() != 1) {
                        return $this->setError(403);
                    }
                }

                break;
                // End Sub Groups


            default:
                // return $this->setSuccess();
                break;
        }

        return $this->setSuccess();
    }


}
