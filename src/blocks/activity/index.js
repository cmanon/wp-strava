/* global wp, wpStrava */
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import metadata from './block.json';

metadata.edit = edit;
metadata.save = () => null;

registerBlockType( metadata.name, metadata );
