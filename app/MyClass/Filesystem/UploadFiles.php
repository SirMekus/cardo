<?php

namespace App\MyClass\Filesystem;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Image;
/**
 * Define a custom exception class
 */
class UploadFiles
{
	//This is the folder from the "public" directory that should be accessible from the web 
	public $docParentFolder;
	public $sub_folder;
	
	
	public $width;
	public $height;
	public $valid_mimes;
	public $max_file_upload_size;//3MB
	public $max_no_of_file_to_upload;
	public $name_of_file;
	
	//These are configurations for outside the web root where errors will be logged
	public $log_file_name = "admin_logs.txt";
	
	// construction function
	//It is assumed we're uploading products for display or sale but can be changed to anything else. This (folder) is actually a sub folder of $docParentFolder located in the public html folder.
	public function __construct($sub_folder="school", $user_defined_valid_mimes=[], $override_mimes = false)
	{
		$this->width = config("settings.file.width");
		$this->height = config("settings.file.height");
		$this->valid_mimes = config("settings.file.mime_type");
		$this->max_file_upload_size = config("settings.file.max_size");
		$this->max_no_of_file_to_upload = config("settings.file.max_to_upload");

		$this->sub_folder = $sub_folder;

		$this->docParentFolder = config('settings.file.directory') ?? 'public';
		
		//We've a list of predefined allowed meme types, User can choose to either add to it or override and create custom
		if($override_mimes == true)
		{
			$this->valid_mimes = $user_defined_valid_mimes;
		}
		else
		{
			if(!empty($user_defined_valid_mimes))
			{
				for($i=0;$i<count($user_defined_valid_mimes);$i++)
				{
					$this->valid_mimes []= $user_defined_valid_mimes[$i];
				}
			}
		}
	}
	
	
    public function unique_name($lenght = 13) 
	{
		$prefix = Str::lower(config("settings.app_name"))."_".date("Ymd");
		// uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($lenght / 2));
        }   
		elseif(function_exists("openssl_random_pseudo_bytes")) 
		{
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } 
		else 
		{
			throw new \Exception("no cryptographically secure random function available");
        }
        return substr($prefix.bin2hex($bytes), 0, $lenght);
    }

	
	
    //Returns an array/string with the renamed file as value.
    function upload($file_upload_name="stock", $ignore=false)
    {
		if(!request()->hasFile($file_upload_name))
		{
			if($ignore == true)
			{
				return;
			}
			else
			{
				abort(422, "Please upload a valid file.");
			}
		}
		
	    $file = request()->file($file_upload_name);
		
		if(is_array($file))
		{
			//We expect a maximum number of $max_no_of_file_to_upload images to upload, if it's more than then we issue an error warning.
            if(count($file) > $this->max_no_of_file_to_upload)
            {
				abort(422, "<p class='alert alert-danger'>Exceeded maximum number of files/images to upload. You can only upload ".$this->max_no_of_file_to_upload." maximum number of files/images.</p>");
            }   
		
			//This will house the new name of the images and will be sent back to the Controller.
	        $photos = [];
		
		    //Users are allowed to upload a certain number of files so that the $_FILES array is filled accordingly. But User can skip the first box for where to place the file in the html form and go for the second, third, etc. In this case the all the arrays will be set and with the same exact count but array["Name"][0] will be empty alongside other than keys pertaining the 0-th file and this will cause a bug in the script so we wanna carter for it first.
		    for($i=0;$i<count($file);$i++)
            {
				if(!empty($file[$i]->getClientOriginalName()))
				{
					if($file[$i]->getMimeType() == null)
		            {
						continue;
		            }
	
	                $file_type = $file[$i]->getMimeType();
			
			        //if file type is not any of these specified types
			        if(!in_array($file_type, $this->valid_mimes))
					{
						abort(422, "<p class='alert alert-danger'>invalid image format detected for ".$file[$i]->getClientOriginalName().".</p>");
			        }
		    
		            if($file[$i]->getSize() > $this->max_file_upload_size)//if file is larger than a specified size.
					{
						abort(422, "<p class='alert alert-danger'>".$file[$i]->getClientOriginalName()." is too large. Please make sure it's less than or equal to 2mb.</p>");
	                }
	
	                $stock = $file[$i]->getClientOriginalName();// takes name of file 'AS IS' from user's computer in this variable.
	                $separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				    if(empty($this->name_of_file))
					{
						$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                        $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				    }
				    else
					{
						//The name is mainly set when it involves upload of document for account verification. The process below helps to mitigate overriding.
					    $index = $i+1;
					    $separate[0] = $this->name_of_file.$index;
				    }
				
	                $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.
                  
					$img = Image::make($file[$i])->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
                              })->encode($separate[1]);
						
					$path = Storage::put($this->docParentFolder.'/'.$this->sub_folder.'/'.$new_name, $img->__toString());
					
                    if($path) 
					{
						$photos []= $new_name;
					}
					else
					{
						abort(422, "Couldn't upload this file");
					}
				}
                    
            }
		    return $photos;
	    }
		
		//This should accomodate for single Upload(s) that don't need to be joined together or kept in an array
		else
		{
			if(!empty($file->getClientOriginalName()))
			{
	            $file_type = $file->getMimeType();
			
			    //if file type is not any of these specified types
			    if(!in_array($file_type, $this->valid_mimes))
				{
					abort(422, "Couldn't upload this file");
			    }
		    
		        if($file->getSize() > $this->max_file_upload_size)//if file is larger than a specified size.
		        {
					abort(422, $file->getClientOriginalName()." is too large. Please make sure it's less than or equal to 2mb");
	            }
	
	            $stock = $file->getClientOriginalName();// takes name of file 'AS IS' from user's computer in this variable.
	            
				$separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				if(empty($this->name_of_file))
				{
					$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                    $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				}
				else
				{
					$separate[0] = $this->name_of_file;
				}
				
	            $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.

                $img = Image::make($file)->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
												$constraint->upsize();
                              })->encode($separate[1]);
							  
				Storage::put($this->docParentFolder.'/'.$this->sub_folder.'/'.$new_name, $img->__toString());
				
				return $new_name;
		    }
		}	
    }



    public function fileDelete($file)
	{
		if(is_array($file))
		{
			for($i=0;$i<count($file);$i++)
			{
				$status = Storage::delete($this->docParentFolder.'/'.$this->sub_folder.'/'.$file[$i]);
		       
		        if(!$status)//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
		        {
					//We log it. We'll manually delete it ourselves.
			        $content = "Failed to delete the image of this upload,'$file[$i]' in ".$this->docParentFolder.'/'.$this->sub_folder.'/'.", ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			        $this->writeToFile($content);
	            }
	       }
        }
        else
		{
			$status = Storage::delete($this->docParentFolder.'/'.$this->sub_folder.'/'.$file);
		 
		    if(!$status)//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
		    {
				//We log it. We'll manually delete it ourselves.
			    $content = "Failed to delete the image of this upload,'$file' in ".$this->docParentFolder.'/'.$this->sub_folder.'/'.", ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			      $this->writeToFile($content);
	        }
		}
		
		return $status;
    }
	
	
	function writeToFile($content)
	{
		Storage::append(config("settings.app_name").'/'.$this->log_file_name, $content);
	}
		
}
?>