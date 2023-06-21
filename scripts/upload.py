
import os
import sys
import argparse
import re
from requests import HTTPError
from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload, BatchHttpRequest


def main(credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath, workspace_delegate_email=''):

    # Build the Google Drive API client using the authenticated credentials
    service = build_google_drive_service(credentials_file, workspace_delegate_email=workspace_delegate_email)
    
    if service:
        try:
            files = get_files(
                service=service, parent_folder_id=parent_folder_id)
            print(files)
            # upload_filename = generate_filename(
            #     files=files, upload_filename=upload_filename)

            # iterate all files from prvided folder id and move them to prodived destination folder(id) one by one
            
            for file in files:
                print(f"Temporary print->file {file}")
                moved_request = move(service=service, file_id=file.get('id'),
                                     destination_folder_id=destination_folder_id)
                moved_file = moved_request.execute()
                print(f"moved file: {moved_file}")

            # ater moving all the file from parent folder now upload new file to parent folder
            upload_request = upload(service=service, parent_folder_id=parent_folder_id,
                                    upload_filepath=upload_filepath, upload_filename=upload_filename)

            uploaded_file = upload_request.execute()
            print(f"uploaded file: {uploaded_file}")

        except HTTPError as error:
            print(f'An error occurred: {error}')
    else:
        print('Unable to load service account credentials.')


# This function will create google drive service to action on google drive
def build_google_drive_service(credentials_file, workspace_delegate_email = ''):
    print(f"Build drive service: {workspace_delegate_email}")
    # Set the scopes that you want to authorize the service account to access - In this case google drive is our scope
    SCOPES = ['https://www.googleapis.com/auth/drive']
    # Load the service account credentials from the credential path
    creds = None
    service = None
    if os.path.exists(credentials_file):
        creds = service_account.Credentials.from_service_account_file(
            credentials_file, scopes=SCOPES)
    if creds is not None:
        delegate_creds = creds.with_subject(workspace_delegate_email) if workspace_delegate_email else creds
        service = build('drive', 'v3', credentials=delegate_creds)
    return service


def get_files(service, parent_folder_id, query=''):
    # Retrieve a list of all file IDs in the parent folder except for the folder type
    query = query if query else "trashed = false and '{}' in parents and mimeType != 'application/vnd.google-apps.folder'".format(
        parent_folder_id)

    results = service.files().list(
        orderBy="modifiedTime desc",
        q=query, fields='files(id,name)', supportsAllDrives=True, includeItemsFromAllDrives = True, corpora='drive', driveId='0ABEpU42fu0P8Uk9PVA').execute()
    print(f"file get results: {results}")
    # file list in a specific folder (google drive folder id)
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

# 'driveId': '0ABEpU42fu0P8Uk9PVA'
# This function will upload file to google drive
def upload(service, parent_folder_id, upload_filepath, upload_filename):
    file_metadata = {'name': upload_filename, 'parents': [parent_folder_id]}
    media = MediaFileUpload(upload_filepath, resumable=True)
    print(f"media: {media}")
    upload_request = service.files().create(body=file_metadata,
                                            media_body=media, fields='id,name',supportsAllDrives=True)
    return upload_request


# This file will move file from source folder to destination folder
def move(service, file_id, destination_folder_id):
    print(f"destination folder id: {destination_folder_id}")
    file = service.files().get(
        fileId=file_id, fields='id, name, parents',supportsAllDrives=True).execute()

    previous_parents = ",".join(file.get('parents', []))
    moved_request = service.files().update(fileId=file_id, addParents=destination_folder_id,
                                           removeParents=previous_parents,
                                           supportsAllDrives=True,
                                           fields='id, parents')
    print(f"move request: {moved_request}")
    return moved_request


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--credentials-file', type=str)
    parser.add_argument('--parent-folder-id', type=str)
    parser.add_argument('--destination-folder-id', type=str)
    parser.add_argument('--upload-filename', type=str)
    parser.add_argument('--upload-filepath', type=str)
    parser.add_argument('--workspace-delegate-email', type=str)
    args = parser.parse_args()

    credentials_file = args.credentials_file
    parent_folder_id = args.parent_folder_id
    destination_folder_id = args.destination_folder_id
    upload_filename = args.upload_filename
    upload_filepath = args.upload_filepath
    workspace_delegate_email = args.workspace_delegate_email
    print(f"delete email {workspace_delegate_email}")
    if not all([credentials_file, parent_folder_id, destination_folder_id, upload_filename, upload_filepath]):
        sys.stderr.write(
            """please provie those arguments value:
--credentials-file,
--parent-folder-id,
--destination-folder-id,
--upload-filename,
--upload-filepath
""")
        sys.exit(1)

    main(credentials_file=credentials_file,
         parent_folder_id=parent_folder_id, destination_folder_id=destination_folder_id, upload_filename=upload_filename, upload_filepath=upload_filepath, workspace_delegate_email=workspace_delegate_email)
