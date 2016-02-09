<?php

namespace IXP\Http\Middleware\Services;

/*
 * Copyright (C) 2009-2016 Internet Neutral Exchange Association Limited.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

use Closure;
use App;

use Illuminate\Http\Request;

use IXP\Services\Grapher as GrapherService;
use IXP\Services\Grapher\Graph;
use IXP\Services\Grapher\Graph\{
    IXP as IXPGraph,
    Infrastructure as InfrastructureGraph
};

use IXP\Exceptions\Services\Grapher\{BadBackendException,CannotHandleRequestException};

/**
 * Grapher -> MIDDLEWARE
 *
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @category   Grapher
 * @package    IXP\Services\Grapher
 * @copyright  Copyright (c) 2009 - 2016, Internet Neutral Exchange Association Ltd
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class Grapher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next )
    {
        // get the grapher service
        $grapher = App::make('IXP\Services\Grapher');

        // all graph requests require a certain basic set of parameters / defaults.
        // let's take care of that here
        $graph = $this->processParameters( $request, $grapher );

        $request->attributes->add(['graph' => $graph]);

        return $next($request);
    }

    /**
     * All graphs have common parameters. We process these here for every request - and set sensible defaults.
     *
     * @param \Illuminate\Http\Request  $request
     */
    private function processParameters( Request $request, GrapherService $grapher ): Graph {

        // while the Grapher service stores the processed parameters in its own object, we update the $request
        // parameters here also just in case we need to final versions later in the request.

        $target = explode( '/', $request->path() );
        $target = array_pop( $target );

        $request->period   = Graph::processParameterPeriod(   $request->input( 'period',   '' ) );
        $request->category = Graph::processParameterCategory( $request->input( 'category', '' ) );
        $request->protocol = Graph::processParameterProtocol( $request->input( 'protocol', 0  ) );
        $request->type     = Graph::processParameterType(     $request->input( 'type',     '' ) );

        switch( $target ) {
            case 'ixp':
                $request->ixp = IXPGraph::processParameterIXP( (int)$request->input( 'ixp', 0 ) )->getId();
                $graph = $grapher->ixp( d2r( 'IXP' )->getDefault() )->setParamsFromArray( $request->all() );
                break;

            case 'infrastructure':
                $request->infrastructure = InfrastructureGraph::processParameterInfrastructure( (int)$request->input( 'infrastructure', 0 ) )->getId();
                $graph = $grapher->infrastructure( d2r( 'Infrastructure' )->find($request->input( 'infrastructure') ) )->setParamsFromArray( $request->all() );
                break;

            default:
                abort(404, 'No such graph type');
        }

        return $graph;
    }

}