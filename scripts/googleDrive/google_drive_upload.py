
import os
import sys
import argparse
import re
from requests import HTTPError
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload


def main(credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath, drive_id, workspace_delegate_email=''):

    # Build the Google Drive API client using the authenticated credentials
    service = build_google_drive_service(credentials_file, workspace_delegate_email=workspace_delegate_email)
    
    if service:
        
        try:
            files = get_files(service=service, parent_folder_id=parent_folder_id, drive_id=drive_id)

            # This pattern will match if there have any extra string after the version number excluding file extension
            # if match for example 3.3.4-upsy or 3.3.4-ocs or 3.3.4-alpha thats means this is not a production zip
            pattern = r'\d+\.\d+\.\d+-(.*?)\.[^.]*$'
            match = re.search(pattern, upload_filename)
            print(f"pattern match result: {match}")
            # iterate all files from prvided folder id and move them to prodived destination folder(id) one by one
            if not match:
                for file in files:
                    moved_request = move(service=service, file_id=file.get('id'), destination_folder_id=destination_folder_id)
                    moved_file = moved_request.execute()
                    print(f"moved file: {moved_file}")
            else:
                folder = get_or_create_folder(service=service, parent_folder_id=parent_folder_id, folder_name=match.group(1))
                parent_folder_id = folder[0].get("id") if folder and folder[0].get("id") else parent_folder_id

            upload_request = upload(service=service, parent_folder_id=parent_folder_id, 
                                    upload_filepath=upload_filepath, upload_filename=upload_filename)
            
            uploaded_file=upload_request.execute()
            print(f"uploaded file: {uploaded_file}")

        except HTTPError as error:
            print(f'An error occurred: {error}')
    else:
        print('Unable to load service account credentials.')


def get_or_create_folder(service, parent_folder_id, folder_name):
    query = "name='{folder_name}' and trashed = false and '{parent_folder_id}' in parents and mimeType='application/vnd.google-apps.folder'".format(
        folder_name=folder_name,
        parent_folder_id=parent_folder_id)
    #retrive files from a specific folder
    results = service.files().list(
        orderBy="modifiedTime desc",
        q=query, fields='files(id,name)', 
        supportsAllDrives=True, 
        includeItemsFromAllDrives=True, 
        corpora='drive', driveId=drive_id).execute()
    print(f"get folders : {results.get('files',[])}")
    files = results.get('files',[])

    if not files:
        folder_metadata = {
            'name': folder_name,
            'mimeType': 'application/vnd.google-apps.folder',
            'parents': [parent_folder_id]
        }
        folder = service.files().create(body=folder_metadata,  fields='id,name', supportsAllDrives=True).execute()
        print(f"create folder : {folder}")
        files = [folder]
    return files
        

        

# This function will create google drive service to action on google drive
def build_google_drive_service(credentials_file, workspace_delegate_email=''):
    # Set the scopes that you want to authorize the service account to access - In this case google drive is our scope
    SCOPES = ['https://www.googleapis.com/auth/drive']
    # Load the service account credentials from the credential path
    creds = None
    service = None

    if os.path.exists(credentials_file):
        creds = service_account.Credentials.from_service_account_file(credentials_file, scopes=SCOPES)

    if creds is not None:
        delegate_creds = creds.with_subject(workspace_delegate_email) if workspace_delegate_email else creds
        service = build('drive', 'v3', credentials=delegate_creds)
    return service


def get_files(service, parent_folder_id, drive_id, query=''):
    # Retrieve a list of all file IDs in the parent folder except for the folder type
    query = query if query else "trashed = false and '{}' in parents and mimeType != 'application/vnd.google-apps.folder'".format(
        parent_folder_id)
    #retrive files from a specific folder
    results = service.files().list(
        orderBy="modifiedTime desc",
        q=query, fields='files(id,name)', 
        supportsAllDrives=True, 
        includeItemsFromAllDrives=True, 
        corpora='drive', driveId=drive_id).execute()

    return results.get('files', [])


def generate_filename(files, upload_filename):
    # Default starting version number
    version_number = '3.0.0'

    if files:
        filename = files[0].get('name')
        match = re.search(r'\d+\.\d+\.\d+', filename)
        if match:
            old_version_number = match.group()
            parts = old_version_number.split('.')
            parts[-1] = str(int(parts[-1]) + 1)
            version_number = '.'.join(parts)

    filename_split = os.path.splitext(upload_filename)
    name_part = filename_split[0]
    extension = filename_split[1]

    upload_filename = f"{name_part}-{version_number}{extension}"

    return upload_filename

# 'driveId': '0ABEpU42fu0P8Uk9PVA' for upsyshopping.com
# This function will upload file to google drive
def upload(service, parent_folder_id, upload_filepath, upload_filename):

    file_metadata = {'name': upload_filename, 'parents': [parent_folder_id]}
    media = MediaFileUpload(upload_filepath, resumable=True)

    upload_request = service.files().create(body=file_metadata, media_body=media, 
                                            fields='id,name', supportsAllDrives=True)
    return upload_request


# This file will move file from source folder to destination folder
def move(service, file_id, destination_folder_id):
    file = service.files().get(fileId=file_id, fields='id, name, parents',
                               supportsAllDrives=True).execute()

    previous_parents = ",".join(file.get('parents', []))
    moved_request = service.files().update(fileId=file_id, addParents=destination_folder_id,
                                           removeParents=previous_parents, supportsAllDrives=True,
                                           fields='id, parents')
    return moved_request


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--credentials-file', type=str)
    parser.add_argument('--parent-folder-id', type=str)
    parser.add_argument('--destination-folder-id', type=str)
    parser.add_argument('--upload-filename', type=str)
    parser.add_argument('--upload-filepath', type=str)
    parser.add_argument('--drive-id', type=str)
    parser.add_argument('--workspace-delegate-email', type=str)
    args = parser.parse_args()

    credentials_file = args.credentials_file
    parent_folder_id = args.parent_folder_id
    destination_folder_id = args.destination_folder_id
    upload_filename = args.upload_filename
    upload_filepath = args.upload_filepath
    drive_id = args.drive_id
    workspace_delegate_email = args.workspace_delegate_email
    
    if not all([credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath, drive_id]):
        sys.stderr.write(
            """please provie those arguments value:
--credentials-file,
--parent-folder-id,
--destination-folder-id,
--upload-filename,
--upload-filepath,
--drive-id
""")
        sys.exit(1)

    main(credentials_file=credentials_file,
         parent_folder_id=parent_folder_id, 
         destination_folder_id=destination_folder_id, 
         upload_filename=upload_filename, 
         upload_filepath=upload_filepath, 
         drive_id=drive_id, 
         workspace_delegate_email=workspace_delegate_email)
