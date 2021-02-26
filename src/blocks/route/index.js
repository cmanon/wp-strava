/* global wp, wpStrava */
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import metadata from './block.json';

metadata.edit = edit;
metadata.save = () => null;

// Leaving this in place causes problems with the toolbar.
delete metadata.apiVersion;

registerBlockType( metadata.name, metadata );
